<?php namespace skyblock\kits;

use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

use core\rank\Structure as SS;
use skyblock\SkyBlockPlayer;

class Kit{

	public function __construct(
		private string $id,
		private string $displayName,

		private array $items = [],
		private array $armor = [],

		private int $cooldown = 1,
		private string $rank = "default",
		private string $shortName = "",
	){}

	public function getId() : string{ return $this->id; }

	public function getDisplayName() : string{ return $this->displayName; }

	public function getShortName() : string{ return $this->shortName; }
	
	public function getItems() : array{ return $this->items; }

	public function getArmor() : array{ return $this->armor; }

	public function getCooldown() : int{ return $this->cooldown; }

	public function getCooldownTime() : int{
		return time() + ($this->cooldown * 60 * 60);
	}

	public function getRank() : string{ return $this->rank; }

	/** @param SkyBlockPlayer $player */
	public function hasRequiredRank(Player $player) : bool{
		return $player->getRankHierarchy() >= SS::RANK_HIERARCHY[$this->getRank()];
	}

	/** @param SkyBlockPlayer $player */
	public function equip(Player $player) : void{
		$ai = $player->getArmorInventory();
		foreach($this->getArmor() as $slot => $piece){
			if($ai->getItem($slot)->isNull()){
				$ai->setItem($slot, $piece);
			}else{
				$player->getInventory()->addItem($piece);
			}
		}

		foreach($this->getItems() as $item){
			$player->getInventory()->addItem($item);
		}
	}

}