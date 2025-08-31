<?php namespace skyblock\crates\event;

use pocketmine\player\Player;

class KeyTransactionEvent extends KeyEvent{

	public $amount = 0;

	public function __construct(?Player $player, string $keytype, int $amount = 0){
		parent::__construct($player, $keytype);
		$this->amount = $amount;
	}

	public function getAmount() : int{
		return $this->amount;
	}

}