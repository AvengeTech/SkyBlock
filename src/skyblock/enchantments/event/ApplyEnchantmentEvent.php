<?php

namespace skyblock\enchantments\event;

use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use skyblock\enchantments\type\Enchantment;

class ApplyEnchantmentEvent extends Event{

	public function __construct(
		private Enchantment $enchantment,
		private Item $item,
		private Player $player
	){}

	public function getEnchantment() : Enchantment{ return $this->enchantment; }

	public function getItem() : Item{ return $this->item; }

	public function getPlayer() : Player{ return $this->player; }
}