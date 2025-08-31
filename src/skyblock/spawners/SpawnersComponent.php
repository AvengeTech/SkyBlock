<?php namespace skyblock\spawners;

use core\session\component\BaseComponent;

class SpawnersComponent extends BaseComponent{

	public bool $isToggled = false;

	public function getName() : string{
		return "spawners";
	}

	public function isToggled() : bool{
		return $this->isToggled;
	}

	public function toggle() : void{
		$this->isToggled = !$this->isToggled();
	}

}