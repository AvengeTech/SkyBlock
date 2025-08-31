<?php namespace skyblock\islands\challenge;

use skyblock\SkyBlock;
use skyblock\islands\Islands;
use skyblock\islands\challenge\command\ChallengesCommand;

use core\Core;
use core\utils\TextFormat;
use Exception;
use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\islands\challenge\ChallengeData as CD;
use skyblock\islands\Island;
use skyblock\SkyBlockPlayer;

class Challenges{

	public array $challenges = [
		1 => [],
		2 => [],
		3 => [],
		4 => [],
		5 => [],
		6 => [],
		7 => [],
		8 => [],
		9 => [],
		10 => [],
		11 => [],
		12 => [],
		13 => [],
		14 => [],
		15 => [],
		16 => [],
		17 => [],
		18 => [],
		19 => [],
		20 => []
	];

	public int $count = -1;

	public function __construct(public Islands $islands, SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->register("challenges", new ChallengesCommand($plugin, "challenges", "Access your challenge progress"));
		if(Core::thisServer()->isSubServer() || $islands->getIslandManager()->loadIslandsOnMain()){
			$plugin->getServer()->getPluginManager()->registerEvents(new ChallengeListener($plugin, $this), $plugin);
		}
		$this->loadChallenges();
		echo $this->getChallengeCount() . " challenges setup!", PHP_EOL;
	}

	public function loadChallenges() : void{
		foreach(CD::CHALLENGES as $level => $challenges){
			if($level > 15) return;

			foreach($challenges as $id => $data){
				$name = $data["name"];
				$description = $data["description"];
				$techits = $data["techits"];
				$difficulty = $data["difficulty"];

				if(!isset($data["class"])) continue;

				$class = ChallengeManager::CLASS_NAMESPACE . $level . "\\" . $data["class"];
				$progress = $data["progress"];

				$this->challenges[$level][$id] = new $class($id, $name, $class, $description, $level, $techits, $difficulty, $progress);
			}
		}
	}

	public function getChallengeCount() : int{
		if($this->count !== -1) return $this->count;
		$count = 0;
		foreach($this->challenges as $level){
			foreach($level as $challenge){
				$count++;
			}
		}
		return $this->count = $count;
	}

	/** @return Challenge[] */
	public function getChallenges(int $level) : array{
		$challenges = [];
		$mc = $this->challenges[$level] ?? [];
		foreach($mc as $id => $challenge){
			$challenges[$id] = clone $challenge;
		}
		return $challenges;
	}

	public function getChallenge(int $id) : ?Challenge{
		for($i = 1; $i <= 15; $i++){
			$challenges = $this->getChallenges($i);
			foreach($challenges as $challenge){
				if($challenge->getId() === $id){
					return $challenge;
				}
			}
		}
		return null;
	}

	/** @param SkyBlockPlayer $player */
	public function process(Island $island, Event $e, Player $player, array $ids) : self{
		$csession = $island->getChallengeManager();
		foreach($ids as $id){
			$challenge = $this->getChallenge($id);

			if(is_null($challenge) || !$csession->hasLevelUnlocked($challenge->getUnlockLevel())) continue;

			try{
				$csession->getLevelSession($challenge->getUnlockLevel())->getChallengeById($id)->event($e, $player);
			}catch(Exception $exception){
				if($player->isTier3()){
					$player->sendMessage(TextFormat::RED . $exception->getMessage());
				}

				$player->sendMessage(TextFormat::RI . "The challenge \"" . $challenge->getName() . "\" is broken, report this in the #Bugs channel or in a ticket on the " . TextFormat::BLUE . "Discord" . TextFormat::GRAY . ". (Challenge ID: {$id})");
			}
		}
		return $this;
	}
}