<?php

namespace skyblock\hoppers;


use pocketmine\block\{
	Hopper as VanillaHopper,
	Block
};
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\{
	Container,
	Tile,
	Furnace,
};
use core\block\tile\Chest;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\BlockTransaction;
use pocketmine\player\Player;
use skyblock\SkyBlock;
use skyblock\hoppers\tile\HopperTile;

class HopperBlock extends VanillaHopper {

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
		parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);

		$tile = $this->getPosition()->getWorld()->getTileAt(
			$this->getPosition()->x, 
			$this->getPosition()->y, 
			$this->getPosition()->z
		);

		if($tile instanceof HopperTile){
			/** @var Tile $tile */
			SkyBlock::getInstance()->hopperStore[$tile->getPosition()->__toString()] = $tile;
		}

		return true;
	}

	public function getInventory(): ?HopperInventory {
		$tile = $this->getPosition()->getWorld()->getTileAt(
			$this->getPosition()->x, 
			$this->getPosition()->y, 
			$this->getPosition()->z
		);

		return $tile instanceof HopperTile ? $tile->getInventory() : null;
	}

	public function getContainerAbove(): ?Container {
		return $this->getPosition()->getWorld()->getTile($this->getSide(Facing::UP)->getPosition());
	}

	public function getContainerBelow(): ?Container {
		return $this->getPosition()->getWorld()->getTile($this->getSide($this->getFacing())->getPosition());
	}

	public function getLowestFreeContainer(?Item $item = null, int $maxChecks = 5): ?Container {
		if(is_null($item)) return null;

		$time = microtime(true);
		$container = $this->getContainerBelow();

		if($container instanceof Container){
			if($container->getInventory()->canAddItem($item)){
				while(!is_null($container) && $maxChecks > 0){
					if($container instanceof HopperTile){
						/** @var self $below */
						$below = $container->getBlock();

						if(
							!is_null($below->getContainerBelow()) &&
							$below->getInventory()->canAddItem($item)
						){
							$container = $below;
							$maxChecks--;
							continue;
						}else{
							break;
						}
					}elseif($container instanceof Chest){
						if($container->isPaired()){
							$containers = [$container, $container->getPair()];

							/** @var Tile[] $containers */
							foreach($containers as $index => $chest){
								if(
									($tile = $chest->getPosition()->getWorld()->getTile(
										$chest->getPosition()->getSide(Facing::DOWN)
									)) instanceof HopperTile && $tile->getInventory()->canAddItem($item)
								){
									$check[$index] = $tile;
								}else{
									unset($check[$index]);
								}
							}

							if(count($check) > 1){
								$container = $check[array_rand($check)];
								$maxChecks--;
								continue;
							}elseif(count($check) == 1){
								$container = array_shift($check);
								$maxChecks--;
								continue;
							}else{
								break;
							}
						}else{
							/** @var Tile $container */
							if(
								($tile = $container->getPosition()->getWorld()->getTile(
									$container->getPosition()->getSide(Facing::DOWN)
								)) instanceof HopperTile && $tile->getInventory()->canAddItem($item)
							){
								$container = $tile;
								$maxChecks--;
								continue;
							}else{
								break;
							}
						}
					}else{
						break;
						//maybe do something here?
					}
				}
			}else{
				$container = null;
			}
		}
		echo "TIME TO DO HOPPER CHECK (Had " . $maxChecks . " remaining checks): " . microtime(true) - $time, PHP_EOL;
		return $container;
	}

	public function onScheduledUpdate() : void {
		if(!is_null($this->getInventory())){
			$hopperPulled = false;

			// PULL ENTITY ITEMS
			$bb = new AxisAlignedBB(
				$this->getPosition()->x,
				$this->getPosition()->y,
				$this->getPosition()->z,
				$this->getPosition()->x + 1,
				$this->getPosition()->y + 1.5,
				$this->getPosition()->z + 1
			);
			$ecount = 0;

			foreach($this->getPosition()->getWorld()->getNearbyEntities($bb) as $entity){
				if(
					!$entity instanceof ItemEntity ||
					$entity->isFlaggedForDespawn() ||
					$entity->isClosed()
				) continue;

				$item = $entity->getItem();

				if(!$item instanceof Item) continue;

				if($item->isNull()){
					$entity->kill();
					continue;
				}

				$newItem = clone $item;
				$count = min($newItem->getCount(), (mt_rand(1, 8) * 8));
				$newItem->setCount($count);

				if($this->getInventory()->canAddItem($newItem)){
					$this->getInventory()->addItem($newItem);

					$item->pop($count);

					if($item->getCount() <= 0) $entity->flagForDespawn();

					$ecount++;
				}
			}

			// PULL DOWNWARDS
			$up = $this->getSide(Facing::UP);

			if(
				($tile = $this->getPosition()->getWorld()->getTile(
					$up->getPosition()
				)) instanceof Container
			){
				if($tile instanceof Furnace){
					$item = $tile->getInventory()->getItem(2);
					$newItem = clone $item;
					$newItem->setCount(1);

					if($this->getInventory()->canAddItem($newItem)){
						$this->getInventory()->addItem($newItem);
						$tile->getInventory()->removeItem($newItem);
					}
				}elseif($tile instanceof Chest || $tile instanceof HopperTile){
					$count = mt_rand(1, 8) * 8;

					foreach($tile->getInventory()->getContents() as $item){
						if($count <= 0) break;

						$newItem = clone $item;
						$amount = min($newItem->getCount(), $count);
						$newItem->setCount($amount);

						if($this->getInventory()->canAddItem($newItem)) {
							$this->getInventory()->addItem($newItem);
							$tile->getInventory()->removeItem($newItem);

							$count -= $amount;

							if($tile instanceof HopperTile && !$hopperPulled) $hopperPulled = true;
							break;
						}
					}
				}
			}

			// PUSH TO DIRECTION
			$direction = $this->getSide($this->getFacing());

			if(
				($tile = $this->getPosition()->getWorld()->getTile(
					$direction->getPosition()
				)) instanceof Container
			){
				if($tile instanceof Furnace){
					$slot = ($this->getFacing() === Facing::DOWN ? 0 : 1);
					$furanceItem = $tile->getInventory()->getItem($slot);

					foreach($this->getInventory()->getContents() as $item){
						$newItem = clone $item;

						$min = ($furanceItem->isNull() ? $newItem->getCount() : ($furanceItem->getMaxStackSize() - $furanceItem->getCount()));

						$newItem->setCount(min($min, min($newItem->getCount(), (mt_rand(1, 8) * 8))));

						if(
							$tile->getInventory()->canAddItem($newItem) &&
							(
								$slot == 0 || 
								$slot == 1 && $newItem->getFuelTime() > 0
							)
						){
							$this->getInventory()->removeItem($newItem);

							$tile->getInventory()->setItem(
								$slot, 
								($furanceItem->isNull() ? $newItem : $furanceItem)->setCount(min(
									($furanceItem->isNull() ? $newItem->getMaxStackSize() : $furanceItem->getMaxStackSize()),
									($furanceItem->isNull() ? 0 : $furanceItem->getCount()) + $newItem->getCount()	
								))
							);
						}
					}
				}else{
					if(
						$hopperPulled &&
						$this->getPosition()->getWorld()->getTile(
							$this->getSide(Facing::DOWN)->getPosition()
						) instanceof HopperTile
					) return;

					$count = mt_rand(1, 8) * 8;

					foreach($this->getInventory()->getContents() as $item){
						if($count <= 0) break;

						$newItem = clone $item;
						$amount = min($newItem->getCount(), $count);
						$newItem->setCount($amount);

						if($tile->getInventory()->canAddItem($newItem)){
							$this->getInventory()->removeItem($newItem);
							$tile->getInventory()->addItem($newItem);

							$count -= $amount;
						}
					}
				}
			}
		}

		return;
	}

	public function onScheduledUpdateNew(): void { //todo: GET WORKING GOT DAYUMITE
		if ($this->getInventory() !== null) {
			$time = microtime(true);
			$fromAbove = 0;
			$toBelow = 0;

			$up = $this->getSide(Facing::UP);
			if (($tile = $this->getContainerAbove()) !== null) {
				if ($tile instanceof Furnace) {
					$item = $tile->getInventory()->getItem(2);
					if ($this->getInventory()->canAddItem($item)) {
						$lowest = $this->getLowestFreeContainer($item);
						if ($lowest !== null) {
							$lowest->getInventory()->addItem($item);
						} else {
							$this->getInventory()->addItem($item);
						}
						$tile->getInventory()->removeItem($item);
						$fromAbove++;
					}
				} elseif ($tile instanceof Chest) {
					$lowest = $this->getLowestFreeContainer();
					if ($lowest === null) {
						foreach ($tile->getInventory()->getContents() as $item) {
							if ($this->getInventory()->canAddItem($item)) {
								$this->getInventory()->addItem($item);
								$tile->getInventory()->removeItem($item);
								$fromAbove++;
							}
						}
					} else {
						foreach ($tile->getInventory()->getContents() as $item) {
							if ($lowest->getInventory()->canAddItem($item)) {
								$lowest->getInventory()->addItem($item);
								$tile->getInventory()->removeItem($item);
								$fromAbove++;
							} else {
								$lowest = $this->getLowestFreeContainer();
								if ($lowest === null) break;
							}
						}
					}
				}
			} else {
				$bb = new AxisAlignedBB($this->getPosition()->x, $this->getPosition()->y, $this->getPosition()->z, $this->getPosition()->x + 1, $this->getPosition()->y + 1.5, $this->getPosition()->z + 1);
				$ecount = 0;

				$lowest = $this->getLowestFreeContainer();
				if ($lowest === null) {
					foreach ($this->getPosition()->getWorld()->getNearbyEntities($bb) as $entity) {
						if (!$entity instanceof ItemEntity || $entity->isFlaggedForDespawn() || $entity->isClosed()) {
							continue;
						}

						$item = $entity->getItem();
						if ($item instanceof Item) {
							if ($item->isNull()) {
								$entity->kill();
								continue;
							}
							if ($this->getInventory()->canAddItem($item)) {
								$this->getInventory()->addItem($item);
								$entity->flagForDespawn();
								$ecount++;
								$fromAbove++;
							}
						}
					}
				} else {
					foreach ($this->getPosition()->getWorld()->getNearbyEntities($bb) as $entity) {
						if (!$entity instanceof ItemEntity || $entity->isFlaggedForDespawn() || $entity->isClosed()) {
							continue;
						}

						$item = $entity->getItem();
						if ($item instanceof Item) {
							if ($item->isNull()) {
								$entity->kill();
								continue;
							}
							if ($lowest->getInventory()->canAddItem($item)) {
								$lowest->getInventory()->addItem($item);
								$entity->flagForDespawn();
								$ecount++;
								$fromAbove++;
							} else {
								$lowest = $this->getLowestFreeContainer();
								if ($lowest === null) break;
							}
						}
					}
				}
			}

			if (count($this->getInventory()->getContents()) > 0) {
				if (($tile = $this->getContainerBelow()) instanceof Container) {
					$added = false;
					if ($tile instanceof Furnace) {
						foreach ($this->getInventory()->getContents() as $item) {
							if ($item->getFuelTime() !== 0) {
								$slot = 1;
							} else {
								$slot = 0;
							}

							$air = true;
							$slotitem = $tile->getInventory()->getItem($slot);
							if ($slotitem->isNull()) {
								$air = false;
								if (!$slotitem->equals($item, true)) {
									continue;
								}
							}

							if ($slotitem->getCount() + $item->getCount() > $slotitem->getMaxStackSize()) {
								continue;
							}

							if ($air) {
								$tile->getInventory()->setItem($slot, $item);
							} else {
								$new = clone $slotitem;
								$new->setCount($new->getCount() + $item->getCount());
								$tile->getInventory()->setItem($slot, $new);
							}
							$this->getInventory()->removeItem($item);
							break;
						}
					} else {
						$lowest = $this->getLowestFreeContainer();
						if ($lowest === null) {
							foreach ($this->getInventory()->getContents() as $item) {
								if ($tile->getInventory()->canAddItem($item)) {
									$tile->getInventory()->addItem($item);
									$this->getInventory()->removeItem($item);
								}
							}
						} else {
							foreach ($this->getInventory()->getContents() as $item) {
								if ($lowest->getInventory()->canAddItem($item)) {
									$lowest->getInventory()->addItem($item);
									$tile->getInventory()->removeItem($item);
								} else {
									$lowest = $this->getLowestFreeContainer();
									if ($lowest === null) break;
								}
							}
						}
					}
				}
			}

			if ($fromAbove > 0 || $toBelow > 0)
				echo "TICK NEW HOPPER (" . $fromAbove . " item stacks from above, " . $toBelow . " sent below): " . microtime(true) - $time, PHP_EOL;
		}
	}
}
