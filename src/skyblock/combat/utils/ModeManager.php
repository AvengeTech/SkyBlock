<?php namespace skyblock\combat\utils;

use core\AtPlayer;
use pocketmine\{
	Server,
	player\Player
};

use skyblock\combat\CombatComponent;

use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class ModeManager{

	const DISABLED_COMMANDS = [
		"spawn", "hub", "crates", "reconnect",
		"feed", "fix", "repair",
		"koth", "arena",
		"island", "is", "i",
		"leaderboards", "lb",
		"trash", "ot",
		"wz", "arena", "warzone",
		"ec", "enderchest", "echest",
		'vp', 'voteparty',
		"pvp", "combat"
	];

	public int $combattime = 0;
	public ?string $hit = null;

	public function __construct(public CombatComponent $session){}

	public function getSession() : CombatComponent{
		return $this->session;
	}

	public function tick() : void{
		if($this->combattime > 0){
			$this->combattime--;

			if($this->combattime <= 0){
				$this->setHit();
				$this->getSession()->getPlayer()->sendMessage(TextFormat::YI . "You are no longer in combat mode!");
			}
		}
	}

	public function reset(bool $send = true) : void{
		$this->combattime = 0;
		$this->setHit();
		if($send) $this->getSession()->getPlayer()->sendMessage(TextFormat::YI . "You are no longer in combat mode!");
	}

	public function punish() : void{
		/** @var SkyBlockPlayer $player */
		$player = $this->getSession()->getPlayer();
		$player->takeTechits(50);

		/** @var SkyBlockPlayer $hit */
		$hit = $this->getHit();
		if($hit instanceof Player){
			$hsession = $hit->getGameSession()->getCombat();
			$hsession->kill($player);
		}
	}

	public function getCombatTime() : int{
		return $this->combattime;
	}

	public function inCombat() : bool{
		return $this->combattime > 0 && $this->getHit() !== null;
	}

	public function setCombat(Player $player, int $time = 10) : void{
		$this->setHit($player);
		$this->combattime = $time;
	}

	public function getHit() : ?AtPlayer{
		return Server::getInstance()->getPlayerExact($this->hit ?? "%%impossiblename%%");
	}

	public function setHit(?Player $player = null) : void{
		if($player !== null){
			$this->hit = $player->getName();
		}else{
			$this->hit = null;
		}
	}

}