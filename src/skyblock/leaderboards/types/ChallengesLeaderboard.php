<?php namespace skyblock\leaderboards\types;

use pocketmine\utils\TextFormat;
use pocketmine\{
	Server
};

use skyblock\{
	SkyBlockPlayer
};

class ChallengesLeaderboard extends Leaderboard{

	public function getType() : string{
		return "challenges";
	}

	public function calculate() : void{
		$texts = [];

		$texts[] = TextFormat::AQUA . TextFormat::BOLD . "Most Completed Challenges";

		$top = [];
		/** @var SkyBlockPlayer $player */
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			if($player->isLoaded() && !$player->isVanished() && !$this->inLeft($player)){
				$session = ($gs = $player->getGameSession())->getIslands();
				if(!(is_null(($island = $session->getIslandAt())))){
					$top[$player->getName()] = $island->getChallengeManager()->getTotalChallengesCompleted();
				}
			}
		}
		arsort($top);

		$i = 1;
		while(true){
			$name = key($top);
			$level = array_shift($top);

			$texts[$name] = TextFormat::RED . $i . ". " . TextFormat::YELLOW . $name . " " . TextFormat::GRAY . "- " . TextFormat::AQUA . $level;

			$i++;

			if(empty($top) || count($texts) > $this->getSize()) break;
		}

		$this->texts = $texts;

		$this->updateSpawnedTo();
	}

}