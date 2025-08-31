<?php namespace skyblock\crates\event;

use pocketmine\player\Player;

use pocketmine\event\Event;

class KeyEvent extends Event{

	public $player;

	public $keytype;

	public function __construct(?Player $player, string $keytype){
		$this->player = $player;
		$this->keytype = $keytype;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getKeyType() : string{
		return $this->keytype;
	}

}