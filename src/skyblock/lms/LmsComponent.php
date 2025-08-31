<?php namespace skyblock\lms;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class LmsComponent extends SaveableComponent{

	public ?Game $game = null;

	/** @var LmsStat[] */
	public array $stats = [];

	public int $cooldown = 0;
	
	public function getName() : string{
		return "lms";
	}
	
	public function getGame() : ?Game{
		return $this->game;
	}
	
	public function inGame() : bool{
		return $this->game !== null;
	}
	
	public function setGame(?Game $game = null) : void{
		$oldGame = $this->getGame();
		$this->game = $game;
		if($game === null && $oldGame !== null){
			$player = $this->getPlayer();
			$oldGame->removePlayer($player);
			$oldGame->removeSpectator($player);
			$oldGame->removeScoreboard($player);
		}
	}

	public function getStats(int $type = LmsStat::TYPE_ALLTIME) : ?LmsStat{
		return isset($this->stats[$type]) ? $this->stats[$type] : null;
	}

	public function getKills(int $type = LmsStat::TYPE_ALLTIME) : int{
		return isset($this->stats[$type]) ? ($this->stats[$type]?->getKills() ?? 0) : 0;
	}

	public function addKill() : void{
		foreach($this->stats as $stat){
			$stat->addKill();
		}
	}

	public function getDeaths(int $type = LmsStat::TYPE_ALLTIME) : int{
		return isset($this->stats[$type]) ? ($this->stats[$type]?->getDeaths() ?? 0) : 0;
	}

	public function addDeath() : void{
		foreach($this->stats as $stat){
			$stat->addDeath();
		}
	}

	public function getWins(int $type = LmsStat::TYPE_ALLTIME) : int{
		return isset($this->stats[$type]) ? ($this->stats[$type]?->getWins() ?? 0) : 0;
	}

	public function addWin() : void{
		foreach($this->stats as $stat){
			$stat->addWin();
		}
	}
	
	public function hasCooldown() : bool{
		return $this->getCooldown() >= time();
	}

	public function getCooldown() : int{
		return $this->getStats()->getCooldown();
	}

	public function setCooldown() : void{
		$this->getStats()->setCooldown(time() + (60 * 60 * 1));
	}

	public function getFormattedCooldown() : string{
		$cooldown = $this->getCooldown() - time();
		$dtF = new \DateTime("@0");
		$dtT = new \DateTime("@$cooldown");
		return $dtF->diff($dtT)->format("%h hours, %i minutes");
	}

	public function resetStats(int $type = LmsStat::TYPE_WEEKLY) : void{
		$stat = $this->getStats($type);
		if($stat !== null){
			$stat->setKills(0);
			$stat->setDeaths(0);
			$stat->setWins(0);
		}
	}

	public function delete() : void{
		foreach($this->stats as $stat){
			$stat->setKills(0);
			$stat->setDeaths(0);
			$stat->setWins(0);
		}
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"DROP TABLE IF EXISTS lms_stats",
			"CREATE TABLE IF NOT EXISTS lms_stats(
				xuid BIGINT(16) NOT NULL,
				ttype INT NOT NULL,
				kills INT NOT NULL DEFAULT 0,
				deaths INT NOT NULL DEFAULT 0,
				wins INT NOT NULL DEFAULT 0,
				cooldown INT NOT NULL DEFAULT 0,
				PRIMARY KEY(xuid, ttype)
			)",
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM lms_stats WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$result = $request->getQuery()->getResult();
		$rows = $result->getRows();
		foreach($rows as $row){
			$stats = new LmsStat(
				$this,
				$type = $row["ttype"],
				$row["kills"], $row["deaths"], $row["wins"],
				$row["cooldown"]
			);
			$this->stats[$type] = $stats;
		}
		for($i = 0; $i <= 2; $i++){
			if(!isset($this->stats[$i])){
				$this->stats[$i] = new LmsStat($this, $i);
			}
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();
		return $this->getKills() !== $verify["kills"] ||
			$this->getDeaths() !== $verify["deaths"] ||
			$this->getWins() !== $verify["wins"];
	}

	public function saveAsync() : void{
		if(!$this->isLoaded()) return;

		$this->setChangeVerify([
			"kills" => $this->getKills(),
			"deaths" => $this->getDeaths(),
			"wins" => $this->getWins(),
		]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), []);
		foreach($this->stats as $stat){
			if($stat->hasChanged()){
				$request->addQuery($stat->getQuery($this->getUser()));
				$stat->setChanged(false);
			}
		}
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function finishSaveAsync() : void{
		parent::finishSaveAsync();
		//todo: check if stats changed during save process?
	}

	public function save() : bool{
		if(!$this->isLoaded()) return false;

		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach($this->stats as $stat){
			if($stat->hasChanged()){
				$stat->mtSave($this->getUser(), $db);
				$stat->setChanged(false);
			}
		}

		return parent::save();
	}

	public function getSerializedData(): array {
		$stats = [];
		foreach ($this->stats as $stat) {
			$stats[] = [
				"ttype" => $stat->type,
				"kills" => $stat->getKills(),
				"deaths" => $stat->getDeaths(),
				"wins" => $stat->getWins(),
				"cooldown" => $stat->getCooldown()
			];
		}
		return [
			"stats" => $stats
		];
	}

	public function applySerializedData(array $data): void {
		foreach ($data["stats"] as $stat) {
			$stats = new LmsStat(
				$this,
				$type = $stat["ttype"],
				$stat["kills"],
				$stat["deaths"],
				$stat["wins"],
				$stat["cooldown"]
			);
			$this->stats[$type] = $stats;
		}
		for ($i = 0; $i <= 2; $i++) {
			if (!isset($this->stats[$i])) {
				$this->stats[$i] = new LmsStat($this, $i);
			}
		}
	}

}