<?php namespace skyblock\islands\shop;

use pocketmine\block\tile\{
	Sign
};
use core\block\tile\Chest;
use skyblock\islands\Island;

class ShopManager{

	const SHOPS_PER_LEVEL = 1;

	public function __construct(
		public Island $island,
		public array $shops = []
	){}

	public function save(bool $async = true) : void{
		foreach($this->getShops() as $shop){
			$shop->save($async);
		}
	}

	/**
	 * Used if island is loaded before island world
	 */
	public function resetPositions() : void{
		foreach($this->getShops() as $shop){
			$shop->updatePosition(
				$shop->getPosition()->getX(),
				$shop->getPosition()->getY(),
				$shop->getPosition()->getZ(),
			);
		}
	}

	public function getIsland() : Island{
		return $this->island;
	}

	public function getShopLimit() : int{
		if(($island = $this->getIsland())->getSizeLevel() < 5) return 0;

		return ($island->getSizeLevel() - 4) * self::SHOPS_PER_LEVEL;
	}

	public function getShops() : array{
		return $this->shops;
	}

	public function getShopsFor(int $hierarchy) : array{
		$shops = [];
		foreach($this->getShops() as $shop){
			if($shop->getHierarchy() <= $hierarchy) $shops[$shop->getName()] = $shop;
		}
		return $shops;
	}

	public function getShop(Sign $sign) : ?Shop{
		$key = ($pos = $sign->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
		return $this->shops[$key] ?? null;
	}
	
	public function verify(Shop $shop) : ?Shop{
		return $this->shops[$shop->getKey()] ?? null;
	}
	
	public function getShopByChest(Chest $chest/**, bool $exact = false*/) : ?Shop{
		foreach($this->getShops() as $shop){
			//if($exact){
			//	if($shop->getPosition()->equals($chest->getPosition())){
			//		return $shop;
			//	}
			//}else{
				if($shop->getChest() === $chest){
					return $shop;
				}
			//}
		}
		return null;
	}

	public function addShop(Shop $shop) : void{
		$this->shops[$shop->getKey()] = $shop;
	}

	public function removeShop(Shop $shop, bool $delete = true) : void{
		foreach($this->getShops() as $key => $sh){
			if($sh->getCreated() === $shop->getCreated()){
				unset($this->shops[$key]);
				if($delete){
					$shop->delete();
				}
				return;
			}
		}
	}

	public function delete() : void{
		foreach($this->getShops() as $name => $shop){
			$this->removeShop($shop);
		}
	}

}