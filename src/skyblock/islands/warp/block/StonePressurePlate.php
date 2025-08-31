<?php namespace skyblock\islands\warp\block;

use pocketmine\block\{
	Block,
	StonePressurePlate as PmStonePressurePlate
};
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\{
	AxisAlignedBB,
	Vector3
};
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\{
	ClickSound,
	GhastShootSound
};

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\warp\ui\pad\CreateWarpPadUi;

use core\utils\TextFormat;

class StonePressurePlate extends PmStonePressurePlate{

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			if($player instanceof Player){
				/** @var SkyBlockPlayer $player */
				$is = $player->getGameSession()->getIslands();
				if(!$is->atIsland()){
					return false;
				}
				$island = $is->getIslandAt();
				$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
				if(!$permissions->getPermission(Permissions::EDIT_WARP_PADS)){
					return false;
				}
				$player->showModal(new CreateWarpPadUi($player, $island, $blockReplace->getPosition()));
				return true;
			}
			return false;
		}
		return false;
	}

	public function onEntityInside(Entity $player) : bool{
		/** @var SkyBlockPlayer $player */
		if($player instanceof Player && $player->isLoaded() && ($is = $player->getGameSession()->getIslands())->atIsland()){
			if(!$player->canActivatePressurePlate()) return true;
			$player->setLastPressurePlateActivation();

			$island = $is->getIslandAt();
			$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
			$warp = $island->getWarpManager()->getWarpPadByBlock($this);
			if($warp !== null && ($w = $warp->getWarp()) !== null){
				if($w->getHierarchy() > $permissions->getHierarchy()){
					$player->sendMessage(TextFormat::RI . "You cannot use this warp pad!");
					return true;
				}
				$w->teleportTo($player);

				if(!$player->isVanished()){
					$this->click();
					$this->getPosition()->getWorld()->addSound($this->getPosition(), new GhastShootSound());
				}
			}
		}
		return true;
	}

	public function click() : void{
		$this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
	}

	public static function addData(Item $item) : Item{
		$item->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Warp Pad");

		$lores = [];
		$lores[] = "Place this on your island";
		$lores[] = "and step on it to teleport to";
		$lores[] = "an existing warp!";
		foreach($lores as $key => $lore){
			$lores[$key] = TextFormat::RESET . TextFormat::GRAY . $lore;
		}
		$item->setLore($lores);

		return $item;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [self::addData($this->asItem())];
	}

	public function onNearbyBlockChange() : void{}
	
}