<?php namespace skyblock\crates\event;

use pocketmine\event\Event;

use skyblock\crates\entity\Crate;

class CrateEvent extends Event{

	public function __construct(public Crate $crate){}

	public function getCrate() : Crate{
		return $this->crate;
	}

}