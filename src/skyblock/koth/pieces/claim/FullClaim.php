<?php

namespace skyblock\koth\pieces\claim;

use core\Core;
use core\utils\TextFormat as TF;
use pocketmine\block\utils\DyeColor;
use pocketmine\Server;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class FullClaim extends Claim{

	const TIME_NEEDED = 300; //5 minutes

	private int $time = 0;

	public function tick() : bool{
		foreach($this->game->getPlayers() as $player){
			if(
				$this->game->inCenter($player) && 
				!$player->isVanished() && 
				!$player->isTransferring() && 
				$player->isLoaded()
			){
				if(!($koth = $player->getGameSession()->getKoth())->hasCooldown() || Core::thisServer()->isTestServer()){
					$this->addPlayer($player);
					continue;
				}
					
				$player->sendTip(TF::RED . "You are on KOTH cooldown!" . PHP_EOL . $koth?->getFormattedCooldown());
			}else{
				if($this->isClaimer($player)) $this->setClaimer(null);

				$this->removePlayer($player);
			}
		}

		if(
			!is_null($this->getClaimer()) && (
				empty($this->game->getPlayers()) || 
				!$this->game->inCenter($this->getClaimer()) ||
				!Core::thisServer()->getCluster()->hasPlayer($this->getClaimer())
			)
		) $this->setClaimer(null);

		if(is_null($this->claimer) && !is_null($this->getFirstInQueue())){
			$this->time = time() + self::TIME_NEEDED;

			$this->setClaimer($this->getFirstInQueue());
		}

		if(!is_null($this->claimer)){
			$this->game->setGlassColor(DyeColor::YELLOW());

			if(time() === $this->time - (self::TIME_NEEDED / 2)){
				Core::announceToSS(TF::YI . TF::YELLOW . $this->claimer->getName() . TF::LIGHT_PURPLE . " is at the halfway mark for claiming koth!");
			}

			if(time() >= $this->time){
				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					$player->sendMessage(TF::GI . TF::YELLOW . $this->claimer->getName() . TF::LIGHT_PURPLE . " won the " . TF::AQUA . $this->getGame()->getName() . TF::LIGHT_PURPLE . " KOTH event! " . TF::BOLD . TF::GREEN . "GG");
				}

				$this->getGame()->reward($this->claimer);
				$player->sendTip(TF::GREEN . "You won!");
				return true;
			}else{
				if($this->claimer->isOnline() && $this->claimer->isLoaded()) $this->claimer->sendTip(TF::AQUA . "Claiming... " . TF::YELLOW . gmdate("i:s", time() - ($this->time - self::TIME_NEEDED)) . TF::GRAY . "/" . TF::GREEN . "05:00");
			}
		}else{
			$this->game->setGlassColor(DyeColor::WHITE());
		}

		$this->game->updateScoreboardLines();

		foreach($this->game->getPlayers() as $player){
			if(!$this->isClaimer($player) && !is_null($this->getClaimer())) $player->sendTip(TF::RED . "Claiming: " . $this->getClaimer()->getName());
		}

		return false;
	}

	public function reset() : void{
		$this->claimer = null;
		$this->time = 0;
	}

	public function getTime() : int{ return $this->time; }
}