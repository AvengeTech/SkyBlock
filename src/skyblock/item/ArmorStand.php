<?php namespace skyblock\item;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{
	LevelEventPacket,
	types\LevelEvent
};
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

use skyblock\{
	SkyBlockPlayer
};
use skyblock\entity\ArmorStand as EntityArmorStand;
use skyblock\islands\permission\Permissions;

class ArmorStand extends Item{

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		/** @var SkyBlockPlayer $player */
		$isession = $player->getGameSession()->getIslands();
		if(!$isession->atIsland()){
			return ItemUseResult::FAIL();
		}
		$island = $isession->getIslandAt();
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_ARMOR_STANDS)){
			return ItemUseResult::FAIL();
		}

		$entity = new EntityArmorStand(new Location($blockReplace->getPosition()->x + 0.5, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z + 0.5, $player->getWorld(), $this->getDirection($player->getLocation()->getYaw()), 0), new CompoundTag());
		if($entity instanceof EntityArmorStand){
			if($player->isSurvival()){
				$this->pop();
				$player->getInventory()->setItemInHand($this);
			}
			$entity->spawnToAll();
			$player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelEventPacket::create(LevelEvent::SOUND_ARMOR_STAND_PLACE, 0, $player->getPosition()));
			return ItemUseResult::SUCCESS();
		}
		return ItemUseResult::SUCCESS();
	}

	public function getDirection(float $yaw){
		return (round($yaw / 22.5 / 2) * 45) - 180;
	}

}