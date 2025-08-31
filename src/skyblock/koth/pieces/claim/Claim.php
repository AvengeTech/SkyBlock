<?php

namespace skyblock\koth\pieces\claim;

use skyblock\koth\pieces\Game;
use skyblock\SkyBlockPlayer;

abstract class Claim{

	protected ?SkyBlockPlayer $claimer = null;

	/** @var SkyBlockPlayer[] $players */
	protected array $players = [];

	public function __construct(
		protected Game $game
	){}

	public function getGame() : Game{ return $this->game; }

	public function getClaimer() : ?SkyBlockPlayer{ return $this->claimer; }

	public function setClaimer(?SkyBlockPlayer $claimer) : self{
		$this->claimer = $claimer;

		return $this;
	}

	public function isClaimer(SkyBlockPlayer $player) : bool{
		return !is_null($this->claimer) && $player->getName() === $this->claimer->getName();
	}

	public function addPlayer(SkyBlockPlayer $player) : self{
		$this->players[$player->getName()] = $player;

		return $this;
	}

	public function removePlayer(SkyBlockPlayer $player) : self{
		unset($this->players[$player->getName()]);

		return $this;
	}

	public function inPlayers(SkyBlockPlayer $player) : bool{
		return isset($this->players[$player->getName()]);
	}
	

	public function getFirstInQueue() : ?SkyBlockPlayer{
		foreach($this->players as $player){
			if(!$player->isOnline() || !$player->isLoaded() || $player->isTransferring() || $player->isVanished()) continue;

			return $player;
		}

		return null;
	}

	public function getPlayers() : array{ return $this->players; }

	abstract public function tick() : bool;

	abstract public function reset() : void;


}