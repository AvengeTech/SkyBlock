<?php namespace skyblock\islands\shop;

use pocketmine\item\Item;
use pocketmine\nbt\{
	BigEndianNbtSerializer,
	TreeRoot
};
use pocketmine\player\Player;

use core\inbox\object\MessageInstance;
use core\utils\conversion\LegacyItemIds;
use skyblock\SkyBlockPlayer;

class ShopItem{

	public function __construct(
		public Shop $shop,
		public Item $item,
		public int $price,
	){}

	public function getShop() : Shop{
		return $this->shop;
	}

	public function getItem() : Item{
		return $this->item;
	}
	
	public function getKey() : string{
		return LegacyItemIds::typeIdToLegacyId($this->getItem()->getTypeId()) . ":" . LegacyItemIds::stateIdToMeta($this->getItem());
	}

	public function getPrice() : int{
		return $this->price;
	}

	public function setPrice(int $price) : void{
		$this->price = $price;
		$this->getShop()->setChanged();
	}

	public function getQuantity() : int{
		$chest = $this->getShop()->getChest();
		if($chest === null) return 0;

		$quantity = 0;
		foreach($chest->getInventory()->getContents() as $slot => $item){
			if($item->equals($this->getItem())){
				$quantity += $item->getCount();
			}
		}
		return $quantity;
	}

	public function getQuantityPrice(int $quantity) : int{
		return $this->getPrice() * $quantity;
	}
	
	public function canBuyQuantity(Player $player, int $quantity) : bool{
		$item = clone $this->getItem();
		$item->setCount($quantity);
		return $player->getInventory()->canAddItem($item);
	}

	public function buy(Player $player, int $quantity) : bool{
		/** @var SkyBlockPlayer $player */
		$inbox = false;
		$player->takeTechits($price = $this->getQuantityPrice($quantity));
		$this->getShop()->addBank($price);
		$item = clone $this->getItem();
		$item->setCount($quantity);
		$left = $player->getInventory()->addItem($item);
		if(count($left) > 0){
			($inbox = $player->getSession()->getInbox()->getInbox(1))->addMessage(new MessageInstance(
				$inbox,
				MessageInstance::newId(), 
				time(),
				0,
				"Shop items",
				"Your inventory was full, so some shop items were sent to your inbox!",
				true, false,
				[$item]
			));
			$inbox = true;
		}

		$chest = $this->getShop()->getChest();
		$totalLoops = ceil($quantity / $item->getMaxStackSize());
		$item->setCount($item->getMaxStackSize());
		for($i = 0; $i < $totalLoops; $i++){
			$item->setCount(min($item->getMaxStackSize(), $quantity));
			$chest->getInventory()->removeItem($item);
			$quantity -= $item->getMaxStackSize();
		}

		return $inbox;
	}
	
	public function verify(Shop $shop) : bool{
		return isset($shop->shopItems[$this->getKey()]);
	}
	
	public function asArray() : array{
		$stream = new BigEndianNbtSerializer();
		return [
			"item" => base64_encode($stream->write(new TreeRoot($this->getItem()->nbtSerialize()))),
			"price" => $this->getPrice()
		];
	}
}