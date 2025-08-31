<?php

namespace skyblock\item;

use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

use skyblock\entity\FireworksEntity;

class FireworkRocket extends Fireworks{

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		if(!($player->isGliding())) return ItemUseResult::NONE();

		$this->pop();

		$location = $player->getLocation();
		$entity = new FireworksEntity($location, $this);
		$entity->getNetworkProperties()->setLong(EntityMetadataProperties::MINECART_HAS_DISPLAY, $player->getId());
		$entity->setOwningEntity($player);
		$entity->spawnToAll();

		return ItemUseResult::SUCCESS();
	}
}