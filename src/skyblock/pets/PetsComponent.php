<?php

namespace skyblock\pets;

use core\session\component\ComponentRequest;
use core\session\component\SaveableComponent;
use core\session\mysqli\data\MySqlQuery;
use core\session\mysqli\data\MySqlRequest;
use skyblock\pets\types\EntityPet;
use skyblock\pets\types\IslandPet;
use skyblock\pets\types\PetData;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class PetsComponent extends SaveableComponent{

	/** @var PetData[] $pets */
	private array $pets = [];

	private ?EntityPet $activePet = null;

	public int $needsLoad = -1;

	public function addPet(PetData $data) : self{
		$this->pets[$data->getIdentifier()] = $data;
		return $this;
	}

	public function removePet(int $id) : self{
		if($this->isActivePet($id)){
			$this->activePet->flagForDespawn();
			$this->activePet = null;
		}

		unset($this->pets[$id]);

		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest($this->getXuid(), new MySqlQuery("main", 
				"DELETE FROM pets WHERE xuid=? AND id=?", 
				[$this->getXuid(), $id]
			)),
			function(MySqlRequest $request){}
		);

		return $this;
	}

	/** @return PetData[] */
	public function getPets() : array{ return $this->pets; }

	public function getPet(int $id) : ?PetData{
		return $this->pets[$id] ?? null;
	}

	public function hasPet(int $id) : bool{
		return isset($this->pets[$id]);
	}

	public function getActivePet() : ?EntityPet { return $this->activePet; }

	public function setActivePet(?EntityPet $pet) : self{
		$this->activePet = $pet;
		return $this;
	}

	public function isActivePet(int $id) : bool{
		if(is_null($this->activePet)) return false;
		if(is_null($this->activePet->getPetData())) return false;

		return $this->activePet->getPetData()->getIdentifier() === $id;
	}

	public function loadPet(int $id, ?SkyBlockPlayer $who = null): void {
		$data = $this->pets[$id];

		$data->rest(false);

		$class = Structure::PETS[$id][Structure::DATA_CLASS];
		/** @var IslandPet $pet */
		$pet = new $class(($who ?? $this->getPlayer())->getLocation());
		$pet->setOwner($who ?? $this->getPlayer());
		$pet->setPetData($data);
		$pet->updateNameTag();
		$pet->spawnToAll();

		$this->setActivePet($pet);
		$this->needsLoad = -1;
	}

	public function getName() : string{ return "pets"; }

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();

		foreach([
			// "DROP TABLE pets",
			"CREATE TABLE IF NOT EXISTS pets(
				xuid BIGINT(16) NOT NULL, id INT NOT NULL, name VARCHAR(16), level INT, xp INT, energy FLOAT, active TINYINT(1), lastlogout INT, PRIMARY KEY(xuid, id)
			)"
		] as $query) $db->query($query);
	}

	public function loadAsync(): void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM pets WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void{
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if(count($rows) > 0){
			foreach($rows as $row){
				$id = $row["id"];
				$name = $row["name"];
				$level = $row["level"];
				$xp = $row["xp"];
				$energy = (float) $row["energy"];
				$active = (bool) $row["active"] ?? false;
				$lastlogout = $row["lastlogout"] ?? time();

				$data = new PetData(
					$id, 
					$name ?? Structure::PETS[$id][Structure::DATA_NAME],
					$level ?? 1,
					$xp ?? 0,
					$energy ?? 0,
					!$active,
					$lastlogout
				);
				$this->pets[$id] = $data;

				$data->updateRestEnergy();

				if ($active && !is_null($this->getPlayer())) {
					$data->rest(false);

					$class = Structure::PETS[$id][Structure::DATA_CLASS];
					/** @var IslandPet $pet */
					$pet = new $class($this->getPlayer()->getLocation());
					$pet->setOwner($this->getPlayer());
					$pet->setPetData($data);
					$pet->updateNameTag();
					$pet->spawnToAll();

					$this->setActivePet($pet);
				} elseif ($active && is_null($this->getPlayer())) {
					$this->needsLoad = $id;
				}
			}
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();
		return $this->getPets() !== $verify["pets"];
	}

	public function saveAsync() : void{
		if(!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"pets" => $this->getPets()
		]);

		$queries = [];
		foreach($this->getPets() as $pet){
			$pet->updateRestEnergy();
			$active = (int) ($pet->getIdentifier() === $this->getActivePet()?->getPetData()?->getIdentifier());
			$lastlogout = time();

			$queries[] = new MySqlQuery("pet_" . $pet->getIdentifier(), 
				"INSERT INTO pets(
					xuid, id, name, level, xp, energy, active, lastlogout
				) VALUES(?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
					name=VALUES(name),
					level=VALUES(level),
					xp=VALUES(xp),
					energy=VALUES(energy),
					active=VALUES(active),
					lastlogout=VALUES(lastlogout)",
				[$this->getXuid(), $pet->getIdentifier(), $pet->getName(), $pet->getLevel(), $pet->getXp(), $pet->getEnergy(), $active, $lastlogout]
			);
		}

		if(!is_null(($activePet = $this->activePet)) && !($activePet->isFlaggedForDespawn() || $activePet->isClosed())){
			$activePet->flagForDespawn();
		}

		$request = new ComponentRequest($this->getXuid(), $this->getName(), $queries);
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save() : bool{
		if(!$this->hasChanged() || !$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare(
			"INSERT INTO pets(
					xuid, id, name, level, xp, energy, active, lastlogout
				) VALUES(?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
					name=VALUES(name),
					level=VALUES(level),
					xp=VALUES(xp),
					energy=VALUES(energy),
					active=VALUES(active),
					lastlogout=VALUES(lastlogout)", 
		);

		foreach($this->getPets() as $pet){
			$pet->updateRestEnergy();

			$id = $pet->getIdentifier();
			$name = $pet->getName();
			$level = $pet->getLevel();
			$xp = $pet->getXp();
			$energy = $pet->getEnergy();
			$active = (int) ($id === $this->getActivePet()?->getPetData()->getIdentifier());
			$lastlogout = time();

			$stmt->bind_param("iisiidii", $xuid, $id, $name, $level, $xp, $energy, $active, $lastlogout);
			$stmt->execute();
		}

		$stmt->close();

		if(!is_null(($activePet = $this->activePet)) && !($activePet->isFlaggedForDespawn() || $activePet->isClosed())){
			$activePet->flagForDespawn();
		}

		return parent::save();
	}

	public function getSerializedData() : array{
		$pets = [];
		foreach($this->getPets() as $pet){
			$pets[] = [
				"id" => $pet->getIdentifier(),
				"name" => $pet->getName(),
				"level" => $pet->getLevel(),
				"xp" => $pet->getXp(),
				"energy" => $pet->getEnergy()
			];
		}

		return [
			"pets" => $pets
		];
	}

	public function applySerializedData(array $data) : void{
		$pets = $data["pets"];
		foreach($pets as $pet){
			$id = $pet["id"];
			$name = $pet["name"];
			$level = $pet["level"];
			$xp = $pet["xp"];
			$energy = $pet["energy"];

			$this->pets[$id] = new PetData($id, $name, $level, $xp, $energy);
		}
	}
}