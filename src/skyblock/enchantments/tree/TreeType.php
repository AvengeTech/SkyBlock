<?php namespace skyblock\enchantments\tree;

abstract class TreeType{

	public function __construct(public int $level){}

	public function getLevel() : int{
		return $this->level;
	}

	public function addLevel(int $total = 1) : void{
		$this->level += $total;
	}

	abstract public function apply() : void;
	
}