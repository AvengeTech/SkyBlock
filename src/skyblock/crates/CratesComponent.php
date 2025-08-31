<?php namespace skyblock\crates;

use skyblock\crates\event\{
	KeyGiveEvent,
	KeyTakeEvent
};

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use skyblock\crates\filter\CrateFilter;

class CratesComponent extends SaveableComponent{

	public array $keys = [
		"vote" => 0,
		"iron" => 0,
		"gold" => 0,
		"diamond" => 0,
		"emerald" => 0,
		"divine" => 0
	];
	public int $opened = 0;

	public int $opening = -1;

	public int $instantOpen = -1;

	public ?CrateFilter $filter = null;

	public function getName() : string{
		return "crates";
	}
	
	public function getTotalKeys() : int{
		$total = 0;
		$total = 0;
		foreach($this->getAllKeys() as $type => $amt){
			$total += $amt;
		}
		return $total;
	}

	public function getAllKeys() : array{
		return $this->keys;
	}

	public function getKeys(string $type) : int{
		return $this->keys[$type] ?? 0;
	}

	public function setKeys(string $type, int $value) : void{
		$this->keys[$type] = max(0, $value);
		$this->setChanged();
	}

	public function addKeys(string $type, int $amount = 1) : void{
		$ev = new KeyGiveEvent($this->getPlayer(), $type, $amount);
		$ev->call();

		$this->setKeys($type, $this->getKeys($type) + $amount);
	}

	public function takeKeys(string $type, int $amount = 1) : void{
		$ev = new KeyTakeEvent($this->getPlayer(), $type, $amount);
		$ev->call();

		$this->setKeys($type, $this->getKeys($type) - $amount);
	}

	public function getOpened() : int{
		return $this->opened;
	}

	public function setOpened(int $value) : void{
		$this->opened = $value;
		$this->setChanged();
	}

	public function addOpened(int $value = 1) : void{
		$this->setOpened($this->getOpened() + $value);
	}

	public function isOpening() : bool{
		return $this->opening !== -1;
	}

	public function setOpening(int $crateId = -1){
		$this->opening = $crateId;
	}

	public function getCrateId() : int{
		return $this->opening;
	}

	public function hasInstantOpen() : bool{
		return $this->instantOpen >= time();
	}

	public function setInstantOpen(int $minutes = 10) : void{
		$this->instantOpen = time() + ($minutes * 60);
	}

	public function getFilter(bool $changed = false): CrateFilter {
		if ($changed) $this->setChanged(true); // imma have to do this since all the filter stuff is in a whole different file.

		return $this->filter;
	}

	public function setFilter(CrateFilter $filter): self {
		$this->filter = $filter;

		$this->setChanged(true);
		return $this;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
				//"DROP TABLE IF EXISTS crate_keys",
				"CREATE TABLE IF NOT EXISTS crate_keys(
				xuid BIGINT(16) NOT NULL UNIQUE,
				iron INT NOT NULL DEFAULT 0,
				gold INT NOT NULL DEFAULT 0,
				diamond INT NOT NULL DEFAULT 0,
				emerald INT NOT NULL DEFAULT 0,
				divine INT NOT NULL DEFAULT 0,
				vote INT NOT NULL DEFAULT 0,
				opened INT NOT NULL DEFAULT 0
			)",
				// "DROP TABLE crate_filter",
				"CREATE TABLE IF NOT EXISTS crate_filter(
				xuid BIGINT(16) NOT NULL UNIQUE,
				filterEnabled TINYINT(1) NOT NULL DEFAULT 0,
				inventoryCount INT NOT NULL DEFAULT 0,
				inventoryValue INT NOT NULL DEFAULT 0,
				settings JSON NOT NULL
			)"
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), [
			new MySqlQuery("keys", "SELECT * FROM crate_keys WHERE xuid=?", [$this->getXuid()]),
			new MySqlQuery("filter", "SELECT * FROM crate_filter WHERE xuid=?", [$this->getXuid()])
		]);

		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$keysResult = $request->getQuery("keys")->getResult();
		$keysRows = (array) $keysResult->getRows();
		if (count($keysRows) > 0) {
			$keysData = array_shift($keysRows);
			$this->opened = $keysData["opened"];
			unset($keysData["xuid"], $keysData["opened"]); // remove xuid & opened indexes, breaks everything if not removed
			foreach ($keysData as $type => $_) {
				$this->keys[$type] = $keysData[$type] ?? 0;
			}
		}

		$filterResult = $request->getQuery("filter")->getResult();
		$filterRows = (array) $filterResult->getRows();

		$autoClear = ($this->getUser()->getRankHierarchy() < 6);

		if (count($filterRows) > 0) {
			$filterData = array_shift($filterRows);
			$settings = json_decode($filterData["settings"], false, 512, JSON_OBJECT_AS_ARRAY);

			$this->filter = new CrateFilter(
				(bool) $filterData["filterEnabled"],
				$settings,
				$autoClear,
				$filterData["inventoryCount"],
				$filterData["inventoryValue"]
			);
		} else {
			$this->filter = new CrateFilter(
				false,
				[],
				$autoClear
			);
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();

		return (
			$this->keys !== $verify["keys"] || $this->getOpened() !== $verify["opened"] ||
			$this->filter->isEnabled() !== (bool) $verify["isEnabled"] || $this->filter->getSettings() !== $verify["settings"] || $this->getFilter()->getCount() !== $verify["count"]
		);
	}

	public function saveAsync() : void{
		if(!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"keys" => $this->keys,
			"opened" => $this->getOpened(),
			"isEnabled" => $this->getFilter()->isEnabled(),
			"settings" => $this->getFilter()->getSettings(),
			"count" => $this->getFilter()->getCount()
		]);

		$settings = json_encode($this->getFilter()->getSettings());
		$isEnabled = (int) $this->getFilter()->isEnabled();

		$request = new ComponentRequest($this->getXuid(), $this->getName(), [
			new MySqlQuery(
				"main",
			"INSERT INTO crate_keys(xuid, iron, gold, diamond, emerald, divine, vote, opened) VALUES(?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE iron=VALUES(iron), gold=VALUES(gold), diamond=VALUES(diamond), emerald=VALUES(emerald), divine=VALUES(divine), vote=VALUES(vote), opened=VALUES(opened)",
			[$this->getXuid(), $this->getKeys("iron"), $this->getKeys("gold"), $this->getKeys("diamond"), $this->getKeys("emerald"), $this->getKeys("divine"), $this->getKeys("vote"), $this->getOpened()]
			),
			new MySqlQuery(
				"filter",
				"INSERT INTO crate_filter(
					xuid, 
					filterEnabled, 
					inventoryCount, 
					inventoryValue, 
					settings
				) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				filterEnabled=VALUES(filterEnabled),
				inventoryCount=VALUES(inventoryCount),
				inventoryValue=VALUES(inventoryValue),
				settings=VALUES(settings)",
				[$this->getXuid(), $isEnabled, $this->getFilter()->getCount(), $this->getFilter()->getInventoryValue(), $settings]
			)
		]);
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save() : bool{
		if(!$this->hasChanged() || !$this->isLoaded()) return false;

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$xuid = $this->getXuid();

		$iron = $this->getKeys("iron");
		$gold = $this->getKeys("gold");
		$diamond = $this->getKeys("diamond");
		$emerald = $this->getKeys("emerald");
		$divine = $this->getKeys("divine");
		$vote = $this->getKeys("vote");

		$opened = $this->getOpened();

		$stmt = $db->prepare("INSERT INTO crate_keys(xuid, iron, gold, diamond, emerald, divine, vote, opened) VALUES(?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE iron=VALUES(iron), gold=VALUES(gold), diamond=VALUES(diamond), emerald=VALUES(emerald), divine=VALUES(divine), vote=VALUES(vote), opened=VALUES(opened)");
		$stmt->bind_param("iiiiiiii", $xuid, $iron, $gold, $diamond, $emerald, $divine, $vote, $opened);
		$stmt->execute();
		$stmt->close();

		$isEnabled = (int) $this->getFilter()->isEnabled();
		$inventoryCount = $this->getFilter()->getCount();
		$inventoryValue = $this->getFilter()->getInventoryValue();

		$settings = json_encode($this->getFilter()->getSettings());

		$stmt = $db->prepare("INSERT INTO crate_filter(xuid, filterEnabled, inventoryCount, inventoryValue, settings) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE filterEnabled=VALUES(filterEnabled), inventoryCount=VALUES(inventoryCount), inventoryValue=VALUES(inventoryValue), settings=VALUES(settings)");
		$stmt->bind_param("iiiis", $xuid, $isEnabled, $inventoryCount, $inventoryValue, $settings);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		$settings = json_encode($this->getFilter()->getSettings(), JSON_OBJECT_AS_ARRAY);

		return [
			"iron" => $this->getKeys("iron"),
			"gold" => $this->getKeys("gold"),
			"diamond" => $this->getKeys("diamond"),
			"emerald" => $this->getKeys("emerald"),
			"divine" => $this->getKeys("divine"),
			"vote" => $this->getKeys("vote"),
			"opened" => $this->getOpened(),
			"isEnabled" => (int) $this->getFilter()->isEnabled(),
			"inventoryCount" => $this->getFilter()->getCount(),
			"inventoryValue" => $this->getFilter()->getInventoryValue(),
			"settings" => $settings
		];
	}

	public function applySerializedData(array $data): void {
		foreach ($this->keys as $type => $value) {
			$this->keys[$type] = $data[$type] ?? 0;
		}
		$this->opened = $data["opened"];

		$autoClear = !($this->getUser()->getRankHierarchy() < 6);
		$settings = json_decode($data["settings"], false, 512);

		$this->filter = new CrateFilter(
			(bool) $data["isEnabled"],
			$settings,
			$autoClear,
			$data["inventoryCount"],
			$data["inventoryValue"]
		);
	}

}