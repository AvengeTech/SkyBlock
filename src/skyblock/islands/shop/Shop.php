<?php namespace skyblock\islands\shop;

use pocketmine\block\{
    WallSign
};
use core\block\tile\Chest;
use pocketmine\item\Item;
use pocketmine\math\{
	Facing,
	Vector3
};
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\world\Position;

use skyblock\SkyBlock;

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\conversion\LegacyItemIds;

class Shop{

	const BASE_MAX_SLOTS = 10;

	public bool $changed = false;

	public Position $position;

	public function __construct(
		public ShopManager $shopManager,

		public int $created,

		public string $name,
		public string $description,
		public int $hierarchy,

		Vector3 $vector3,

		public int $bank = 0,
		public array $shopItems = []
	){
		$this->position = Position::fromObject($vector3, $shopManager->getIsland()->getWorld());
	}

	public function getShopManager() : ShopManager{
		return $this->shopManager;
	}

	public function getCreated() : int{
		return $this->created;
	}

	public function getName() : string{
		return $this->name;
	}

	public function setName(string $name) : void{
		$this->name = $name;
		$this->setChanged();
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function setDescription(string $description = "") : void{
		$this->description = $description;
		$this->setChanged();
	}

	public function getHierarchy() : int{
		return $this->hierarchy;
	}

	public function setHierarchy(int $hierarchy = 0) : void{
		$this->hierarchy = $hierarchy;
		$this->setChanged();
	}

	public function getPosition() : Position{
		return $this->position;
	}
	
	public function getKey() : string{
		return ($pos = $this->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
	}

	public function getChest() : ?Chest{
		$sign = ($world = ($pos = $this->getPosition())->getWorld())?->getBlock($this->getPosition());
		if($world === null) return null;
		if($sign === null || !$sign instanceof WallSign) return null;

		return $world->getTile($sign->getSide(Facing::opposite($sign->getFacing()))->getPosition());
	}

	public function updatePosition(int $x, int $y, int $z) : void{
		$this->position = Position::fromObject(new Vector3($x, $y, $z), $this->getPosition()->getWorld());
		$this->setChanged();
	}

	public function getBank() : int{
		return $this->bank;
	}

	public function setBank(int $value) : void{
		$this->bank = $value;
		$this->setChanged();
	}

	public function addBank(int $value = 1) : void{
		$this->setBank($this->getBank() + $value);
	}

	public function takeBank(int $value = 1) : void{
		$this->setBank($this->getBank() - $value);
	}
	
	public function getMaxShopItems() : int{
		return self::BASE_MAX_SLOTS;
	}

	public function getShopItems() : array{
		return $this->shopItems;
	}

	public function addShopItem(ShopItem $item, ?ShopItem $above = null, bool $changed = false) : void{
		if($above === null){
			$this->shopItems[$item->getKey()] = $item;
		}else{
			$newItems = [];
			foreach($this->shopItems as $key => $value) {
				if($key === $above->getKey()){
					$newKey = 'newKey';
					$newValue = 'newValue';
					$newItems[$item->getKey()] = $item;
				}
				$newItems[$key] = $value;
			}
			$this->shopItems = $newItems;
		}
		if($changed) $this->setChanged();
	}

	public function removeShopItem(ShopItem|Item $item) : void{
		if($item instanceof ShopItem){
			$key = $item->getKey();
		}else{
			$key = LegacyItemIds::typeIdToLegacyId($item->getTypeId()) . ":" . LegacyItemIds::stateIdToMeta($item);
		}
		unset($this->shopItems[$key]);
		$this->setChanged();
	}

	public function getShopItemsAsJson() : string{
		$array = [];
		foreach($this->getShopItems() as $shop){
			$array[] = $shop->asArray();
		}
		return json_encode($array);
	}

	public function parseJsonShopItems(string $json) : void{
		try{
			$shopItems = json_decode($json, true);
			$stream = new BigEndianNbtSerializer();
			foreach($shopItems as $key => $shopItem){
				$item = Item::nbtDeserialize($stream->read(base64_decode($shopItem["item"]))->mustGetCompoundTag());
				$itemKey = LegacyItemIds::typeIdToLegacyId($item->getTypeId()) . ":" . LegacyItemIds::stateIdToMeta($item);
				$this->shopItems[$itemKey] = new ShopItem(
					$this,
					$item,
					$shopItem["price"]
				);
			}
		}catch(\Exception $e){}
	}
	
	public function itemInShop(Item $item) : bool{
		foreach($this->shopItems as $shopItem){
			if($shopItem->getItem()->equals($item)) return true;
		}
		return false;
	}
	
	public function verify() : ?Shop{
		return $this->getShopManager()->verify($this);
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_island_shop_" . $this->getShopManager()->getIsland()->getWorldName() . "_" . $this->getName(), new MySqlQuery("main",
				"DELETE FROM island_shops WHERE world=? AND created=?",
				[
					$this->getShopManager()->getIsland()->getWorldName(),
					$this->getCreated(),
				]
			)),
			function(MySqlRequest $request) : void{}
		);
	}

	public function save(bool $async = true) : void{
		if(!$this->hasChanged()) return;
		if($async){
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_shop_" . $this->getShopManager()->getIsland()->getWorldName() . "_" . $this->getName(), new MySqlQuery("main",
					"INSERT INTO island_shops(world, created, name, description, hierarchy, posx, posy, posz, bank, shopitems) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						name=VALUES(name), description=VALUES(description), hierarchy=VALUES(hierarchy),
						posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz),
						bank=VALUES(bank),
						shopitems=VALUES(shopitems)",
					[
						$this->getShopManager()->getIsland()->getWorldName(),
						$this->getCreated(),
						$this->getName(), $this->getDescription(), $this->getHierarchy(),
						$this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ(),
						$this->getBank(),
						$this->getShopItemsAsJson(),
					]
				)),
				function(MySqlRequest $request) : void{
					$this->setChanged(false);
				}
			);
		}else{
			$worldName = $this->getShopManager()->getIsland()->getWorldName();
			$created = $this->getCreated();
			$name = $this->getName();
			$description = $this->getDescription();
			$hierarchy = $this->getHierarchy();
			$x = $this->getPosition()->getX();
			$y = $this->getPosition()->getY();
			$z = $this->getPosition()->getZ();
			$bank = $this->getBank();
			$json = $this->getShopItemsAsJson();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare("INSERT INTO island_shops(world, created, name, description, hierarchy, posx, posy, posz, bank, shopitems) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				name=VALUES(name), description=VALUES(description), hierarchy=VALUES(hierarchy),
				posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz),
				bank=VALUES(bank),
				shopitems=VALUES(shopitems)"
			);
			$stmt->bind_param("sissiiiiis", $worldName, $created, $name, $description, $hierarchy, $x, $y, $z, $bank, $json);
			$stmt->execute();
			$stmt->close();

			$this->setChanged(false);
		}
	}

}