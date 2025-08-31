<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class CaveSpider extends Spider{

	public static function getNetworkTypeId(): string{ return EntityIds::CAVE_SPIDER; }

	public function getName() : string{ return "Cave Spider"; }

	public function getXpDropAmount() : int{ return mt_rand(1, 5); }

}