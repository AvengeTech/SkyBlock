<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Husk extends Zombie{

	public static function getNetworkTypeId(): string{ return EntityIds::HUSK; }

	public function getName() : string{ return "Husk"; }

	public function getXpDropAmount() : int{ return mt_rand(3, 5); }

}
