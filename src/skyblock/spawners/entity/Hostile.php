<?php namespace skyblock\spawners\entity;

abstract class Hostile extends Mob{

	public function getXpDropAmount() : int{
		return 5 + mt_rand(1, 3);
	}

}