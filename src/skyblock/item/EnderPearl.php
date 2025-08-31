<?php namespace skyblock\item;

use pocketmine\entity\{
	Entity,
	Location,
	projectile\Throwable
};
use pocketmine\item\EnderPearl as EP;
use pocketmine\item\{
	ItemIdentifier,
	ItemIds,
	ItemUseResult
};
use pocketmine\player\Player;
use pocketmine\math\Vector3;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\entity\EnderPearl as EnderPearlEntity;

use core\utils\TextFormat;

class EnderPearl extends EP{

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		/** @var SkyBlockPlayer $player */

		if(SkyBlock::getInstance()->getCombat()->getArenas()->inArena($player)){
			$player->sendMessage(TextFormat::RI . "You cannot throw enderpearls in the Arena!");
			return ItemUseResult::FAIL();
		}
		
		if(SkyBlock::getInstance()->getKoth()->inGame($player)){
			$player->sendMessage(TextFormat::RI . "You cannot throw enderpearls during KOTH!");
			return ItemUseResult::FAIL();
		}

		if($player->getGameSession()->getParkour()->hasCourseAttempt()){
			$player->sendMessage(TextFormat::RI . "You cannot throw enderpearls during a parkour attempt!");
			return ItemUseResult::FAIL();
		}

		return parent::onClickAir($player, $directionVector, $returnedItems);
	}

	protected function createEntity(Location $location, Player $thrower) : Throwable{
		return new EnderPearlEntity($location, $thrower);
	}

}