<?php namespace skyblock\data;

use core\network\data\DataSyncQuery;
use core\network\protocol\DataSyncPacket;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\{
	EffectInstance,
};
use pocketmine\block\Air;
use pocketmine\item\{
	Item,
	ItemBlock
};
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\nbt\tag\{
	CompoundTag,
};
use pocketmine\player\Player;

use skyblock\SkyBlock;

use core\session\component\{
	ComponentRequest,
	ComponentSyncRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use RuntimeException;

class DataComponent extends SaveableComponent{

	public int $ticks = 0;
	
	public array $inventory = [];
	public array $armorinventory = [];
	public array $enderchest_inventory = [];
	public array $effects = [];

	public int $health = 20;
	public int $food = 20;
	public int $saturation = 20;

	public int $xplevel = 0;
	public float $xpprogress = 0;

	public function getName() : string{
		return "data";
	}

	public function give() : void{
		$player = $this->getPlayer();
		if(!$player instanceof Player) return;

		$player->getInventory()->setContents($this->getInventoryContents());
		$player->getArmorInventory()->setContents($this->getArmorContents());
		$player->getEnderInventory()->setContents($this->getEnderChestContents());
		foreach($this->getEffects() as $effect){
			$player->addEffect($effect);
		}

		$player->setHealth($player->getHealth());
		$player->getHungerManager()->setFood($this->getFood());
		$player->getHungerManager()->setSaturation($this->getSaturation());

		$player->getXpManager()->setXpLevel($this->getXpLevel());
		$player->getXpManager()->setXpProgress($this->getXpProgress());
	}

	public function update() : void{
		$player = $this->getPlayer();
		if(!$player instanceof Player || !$player->isLoaded()) return;

		$this->inventory = $player->getInventory()->getContents();
		$this->armorinventory = $player->getArmorInventory()->getContents();
		$this->enderchest_inventory = $player->getEnderInventory()->getContents();
		$this->effects = $player->getEffects()->all();

		$this->health = (int) $player->getHealth();
		$this->food = (int) $player->getHungerManager()->getFood();
		$this->saturation = (int) $player->getHungerManager()->getSaturation();

		$this->xplevel = $player->getXpManager()->getXpLevel();
		$this->xpprogress = $player->getXpManager()->getXpProgress();

		$this->setLastUpdateTime(microtime(true));
	}

	public function getInventoryContents() : array{
		return $this->inventory;
	}

	public function getArmorContents() : array{
		return $this->armorinventory;
	}

	public function getEnderChestContents() : array{
		return $this->enderchest_inventory;
	}

	public function getEffects() : array{
		return $this->effects;
	}

	public function getHealth() : int{
		return $this->health;
	}

	public function getFood() : int{
		return $this->food;
	}

	public function getSaturation() : int{
		return $this->saturation;
	}

	public function getXpLevel() : int{
		return $this->xplevel;
	}

	public function getXpProgress() : float{
		return $this->xpprogress;
	}

	public function toString() : string{
		$data = [
			"inventory" => [],
			"armorinventory" => [],
			"enderchest_inventory" => [],
			"effects" => [],

			"health" => $this->getHealth(),
			"food" => $this->getFood(),
			"saturation" => $this->getSaturation(),

			"xplevel" => $this->getXpLevel(),
			"xpprogress" => $this->getXpProgress()
		];
		$stream = new BigEndianNbtSerializer();
		foreach($this->getInventoryContents() as $slot => $item){
			$data["inventory"][$slot] = $stream->write(new TreeRoot($item->nbtSerialize()));
		}
		foreach($this->getArmorContents() as $slot => $item){
			$data["armorinventory"][$slot] = $stream->write(new TreeRoot($item->nbtSerialize()));
		}
		foreach($this->getEnderChestContents() as $slot => $item){
			/** @var Item $item */
			$data["enderchest_inventory"][$slot] = $stream->write(new TreeRoot($item->nbtSerialize()));
		}
		foreach($this->getEffects() as $effect){
			$nbt = CompoundTag::create()->setInt("id", EffectIdMap::getInstance()->toId($effect->getType()))->setInt("duration", $effect->getDuration())->setInt("amplifier", $effect->getAmplifier())->setByte("visible", $effect->isVisible());
			$data["effects"][] = $stream->write(new TreeRoot($nbt));
		}

		return base64_encode(zlib_encode(serialize($data), ZLIB_ENCODING_DEFLATE, 1));
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"DROP TABLE IF EXISTS playerdata",
			"CREATE TABLE IF NOT EXISTS playerdata(xuid BIGINT(16) NOT NULL UNIQUE, data LONGBLOB NOT NULL)",
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT data FROM playerdata WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
		#$this->requestSync();
	}

	public function requestSync(bool $push = false): void {
		return;
		$request = new ComponentSyncRequest(
			$this->getXuid(),
			$this->getName(),
			new DataSyncQuery("main", new DataSyncPacket([
				"xuid" => $this->getXuid(),
				"data" => $this->getSerializedData(),
				"table" => "playerdata",
				"lastUpdate" => $this->getLastUpdateTime(),
				"response" => $push
			]))
		);
		$this->newRequest($request);
		parent::requestSync();
	}

	public function finishSync(?ComponentSyncRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$data = $data["data"];

			$data = unserialize(zlib_decode(base64_decode($data)));
			$stream = new BigEndianNbtSerializer();
			foreach ($data["inventory"] as $slot => $buffer) {
				try {
					$this->inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach ($this->inventory as $slot => $item) {
				if ($item instanceof ItemBlock && $item->getBlock() instanceof Air) unset($this->inventory[$slot]);
			}
			foreach ($data["armorinventory"] as $slot => $buffer) {
				try {
					$this->armorinventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach ($data["enderchest_inventory"] as $slot => $buffer) {
				try {
					$this->enderchest_inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach ($data["effects"] as $effect) {
				$nbt = $stream->read($effect)->mustGetCompoundTag();
				$this->effects[] = new EffectInstance(EffectIdMap::getInstance()->fromId($nbt->getInt("id")), $nbt->getInt("duration"), $nbt->getInt("amplifier"), (bool) $nbt->getByte("visible"));
			}

			$this->health = $data["health"];
			$this->food = $data["food"];
			$this->saturation = $data["saturation"] ?? 20;

			$this->xplevel = $data["xplevel"];
			$this->xpprogress = $data["xpprogress"];
		}

		parent::finishSync($request);
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if(count($rows) > 0){
			$data = array_shift($rows);
			$data = $data["data"];

			$data = unserialize(zlib_decode(base64_decode($data)));
			$stream = new BigEndianNbtSerializer();
			foreach($data["inventory"] as $slot => $buffer){
				try {
					$this->inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach($this->inventory as $slot => $item){
				if($item instanceof ItemBlock && $item->getBlock() instanceof Air) unset($this->inventory[$slot]);
			}
			foreach($data["armorinventory"] as $slot => $buffer){
				try {
				$this->armorinventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach($data["enderchest_inventory"] as $slot => $buffer){
				try {
					$this->enderchest_inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
				} catch (RuntimeException $e) {
				}
			}
			foreach ($data["effects"] as $effect) {
				$nbt = $stream->read($effect)->mustGetCompoundTag();
				$this->effects[] = new EffectInstance(EffectIdMap::getInstance()->fromId($nbt->getInt("id")), $nbt->getInt("duration"), $nbt->getInt("amplifier"), (bool) $nbt->getByte("visible"));
			}

			$this->health = $data["health"];
			$this->food = $data["food"];
			$this->saturation = $data["saturation"] ?? 20;

			$this->xplevel = $data["xplevel"];
			$this->xpprogress = $data["xpprogress"];
		}
		
		parent::finishLoadAsync($request);
	}

	public function saveAsync(bool $update = true): void {
		if(!$this->isLoaded()) return;

		if ($update) $this->update();
		$this->requestSync(true);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT INTO playerdata(xuid, data) VALUES(?, ?) ON DUPLICATE KEY UPDATE data=VALUES(data)", [$this->getXuid(), $this->toString()]));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(bool $update = true): bool {
		if(!$this->isLoaded()) return false;

		if ($update) $this->update();
		$this->requestSync(true);

		$xuid = $this->getXuid();
		$data = $this->toString();

		$db = $this->getSession()->getSessionManager()->getDatabase();

		$stmt = $db->prepare("INSERT INTO playerdata(xuid, data) VALUES(?, ?) ON DUPLICATE KEY UPDATE data=VALUES(data)");
		$stmt->bind_param("is", $xuid, $data);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"data" => $this->toString()
		];
	}

	public function applySerializedData(array $data): void {
		$data = $data["data"];

		$data = unserialize(zlib_decode($data));
		$stream = new BigEndianNbtSerializer();
		foreach ($data["inventory"] as $slot => $buffer) {
			try {
				$this->inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
			} catch (RuntimeException $e) {
			}
		}
		foreach ($this->inventory as $slot => $item) {
			if ($item instanceof ItemBlock && $item->getBlock() instanceof Air) unset($this->inventory[$slot]);
		}
		foreach ($data["armorinventory"] as $slot => $buffer) {
			try {
				$this->armorinventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
			} catch (RuntimeException $e) {
			}
		}
		foreach ($data["enderchest_inventory"] as $slot => $buffer) {
			try {
				$this->enderchest_inventory[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
			} catch (RuntimeException $e) {
			}
		}
		foreach ($data["effects"] as $effect) {
			$effect = $stream->read($effect)->mustGetCompoundTag();
			$this->effects[] = new EffectInstance(EffectIdMap::getInstance()->fromId($effect->getInt("id")), $effect->getInt("duration"), $effect->getInt("amplifier"), (bool) $effect->getByte("visible"));
		}

		$this->health = $data["health"];
		$this->food = $data["food"];
		$this->saturation = $data["saturation"] ?? 20;

		$this->xplevel = $data["xplevel"];
		$this->xpprogress = $data["xpprogress"];
	}

}