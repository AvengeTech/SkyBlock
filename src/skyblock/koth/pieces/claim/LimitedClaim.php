<?php

namespace skyblock\koth\pieces\claim;

use core\Core;
use core\utils\TextFormat as TF;
use pocketmine\block\utils\DyeColor;
use pocketmine\Server;
use skyblock\koth\pieces\Game;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class LimitedClaim extends Claim{

	const DURATION = 600; // 10min
	const FIRST_ANNOUNCEMENT = self::DURATION - (3 * 60);
	const SECOND_ANNOUNCEMENT = self::DURATION - (5 * 60);
	const THIRD_ANNOUNCEMENT = self::DURATION - (8 * 60);

	private array $times = [];
	private int $ticks = 0;
	private int $timeEnd;

	public function __construct(
		Game $game
	){
		parent::__construct($game);

		$this->timeEnd = time() + self::DURATION;
	}

	public function tick() : bool{
		$this->ticks++;

		if(
			$this->timeEnd < time() || 
			!is_null($this->claimer) &&
			isset($this->times[$this->claimer->getXuid()]) &&
			$this->times[$this->claimer->getXuid()] >= (self::DURATION / 2)
		){
			if(empty($this->times)){
				$this->getGame()->end();
				return false;
			}else{
				$first = $this->getFirstPlace();

				if(is_null($first)) return false;

				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					$player->sendMessage(TF::GI . TF::YELLOW . $first->getName() . TF::LIGHT_PURPLE . " won the " . TF::AQUA . $this->getGame()->getName() . TF::LIGHT_PURPLE . " KOTH event! " . TF::BOLD . TF::GREEN . "GG");
				}

				$this->getGame()->reward($first);
				return true;
				
			}
		}

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

		if(is_null($this->claimer) && !is_null($this->getFirstInQueue())) $this->setClaimer($this->getFirstInQueue());

		if(!is_null($this->claimer)){
			if(!isset($this->times[$this->claimer->getXuid()])) $this->times[$this->claimer->getXuid()] = 0;

			$this->game->setGlassColor(DyeColor::YELLOW());

			$this->times[$this->claimer->getXuid()] += 1;


			if($this->claimer->isOnline() && $this->claimer->isLoaded()) $this->claimer->sendTip(TF::AQUA . "Claiming... " . TF::YELLOW . gmdate("i:s", $this->times[$this->claimer->getXuid()]));
		}else{
			$this->game->setGlassColor(DyeColor::WHITE());
		}

		if(
			time() === $this->timeEnd - self::FIRST_ANNOUNCEMENT || 
			time() === $this->timeEnd - self::SECOND_ANNOUNCEMENT || 
			time() === $this->timeEnd - self::THIRD_ANNOUNCEMENT
		){
			if(!is_null(($first = $this->getFirstPlace()))){
				Core::announceToSS(TF::YI . TF::YELLOW . $first->getName() . TF::LIGHT_PURPLE . " currently has the highest claim time with " . TF::YELLOW . gmdate("i:s", $this->times[$first->getXuid()]) . TF::GRAY . '!');
			}
		}

		$this->game->updateScoreboardLines();

		foreach($this->game->getPlayers() as $player){
			if(!$this->isClaimer($player) && !is_null($this->getClaimer())) $player->sendTip(TF::RED . "Claiming: " . $this->getClaimer()->getName());
		}

		return false;
	}

	public function reset() : void{
		$this->times = [];
		$this->claimer = null;
	}

	public function getFirstPlace() : ?SkyBlockPlayer{
		$highest = 0;
		$target = null;

		foreach($this->times as $xuid => $time){
			if($time <= $highest) continue;

			$highest = $time;
			$target = $xuid;
		}

		if(!is_null($target)){
			$player = SkyBlock::getInstance()->getSessionManager()->getSession($target)?->getPlayer();

			if(is_null($player)) unset($this->times[$xuid]);

			return $player;
		}

		return null;
	}

	public function getTimeEnd() : int{ return $this->timeEnd; }

	public function getTimes() : array{ return $this->times; }
}