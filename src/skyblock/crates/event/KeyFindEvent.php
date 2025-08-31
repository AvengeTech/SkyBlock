<?php

namespace skyblock\crates\event;

use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class KeyFindEvent extends KeyEvent{

	use CancellableTrait;

	public function __construct(
		?Player $player,
		string $keytype,
		private int $amount
	){
		parent::__construct($player, $keytype);
	}

	public function addAmount(int $amount) : void{
		$this->amount += max(1, $amount);
	}

	public function subAmount(int $amount) : void{
		$this->amount -= max(1, $amount);
	}

	public function setAmount(int $amount) : void{
		$this->amount = max(1, $amount);
	}

	public function getAmount() : int{
		return $this->amount;
	}
}