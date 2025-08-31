<?php namespace skyblock\islands\challenge\command;

use core\command\type\CoreCommand;
use core\Core;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;
use skyblock\islands\challenge\ui\ChallengeUi;
use skyblock\islands\challenge\ui\LevelsUi;
use skyblock\islands\challenge\ChallengeData;

class ChallengesCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["c", "challenge", "chal", "ch"]);
	}

	public function execute(\pocketmine\command\CommandSender $sender, string $commandLabel, array $args): void {
		if (!$this->hasPermission($sender)) return;
		/** @var Player $sender */
		$isession = $sender->getGameSession()->getIslands();
		if(!$isession->atIsland()){
			$sender->sendMessage(TextFormat::RI . "You must be at an island to use this command!");
			return;
		}
		$csession = $isession->getIslandAt()->getChallengeManager();
		if(count($args) > 0){
			switch($num = array_shift($args)){
				case "ca":
					if(!Core::thisServer()->isTestServer() && !$sender->isTier3()){
						$sender->showModal(new ChallengeUi($sender));
						break;
					}
					foreach($csession->levelSessions as $sess){
						foreach($sess->getChallenges() as $challenge){
							$challenge->setCompleted($sender);
						}
					}
					$isession->getIslandAt()->updateScoreboardLines(false, false, true, false, false);
					$sender->sendMessage(TextFormat::GI . "Force completed all your challenges!");
					break;
				case "da":
					if(!Core::thisServer()->isTestServer() && !$sender->isTier3()){
						$sender->showModal(new ChallengeUi($sender));
						break;
					}
					foreach($csession->levelSessions as $sess){
						foreach($sess->getChallenges() as $challenge){
							$challenge->setCompleted($sender, false);
						}
					}
					$isession->getIslandAt()->updateScoreboardLines(false, false, true, false, false);
					$sender->sendMessage(TextFormat::GI . "Force deleted all your challenges!");
					break;
				case "cc":
					if(!Core::thisServer()->isTestServer() && !$sender->isTier3()){
						$sender->showModal(new ChallengeUi($sender));
						return;
					}
					
					if(!isset($args[0])){
						$sender->sendMessage(TextFormat::GRAY . "/chal cc <id>");
						return;
					}

					$id = $args[0];

					foreach($csession->levelSessions as $sess){
						foreach($sess->getChallenges() as $challenge){
							if($challenge->getId() == $id){
								if($challenge->isCompleted()){
									$sender->sendMessage(TextFormat::RI . "The challenge \"" . $challenge->getName() . "\" is already completed!");
								}else{
									$challenge->setCompleted($sender);
									$sender->sendMessage(TextFormat::GI . "Force completed the challenge \"" . $challenge->getName() . "\"");
								}
								return;
							}
						}
					}

					$isession->getIslandAt()->updateScoreboardLines(false, false, true, false, false);
					$sender->sendMessage(TextFormat::RI . "Could not find challenge with the id {$id}!");
					break;
				case "cl":
					if(!Core::thisServer()->isTestServer() && !$sender->isTier3()){
						$sender->showModal(new ChallengeUi($sender));
						return;
					}

					foreach($csession->levelSessions as $sess){
						foreach($sess->getChallenges() as $challenge){
							if(!$challenge->isCompleted()) $challenge->setCompleted(null);
						}
					}

					$isession->getIslandAt()->updateScoreboardLines(false, false, true, false, false);
					$sender->sendMessage(TextFormat::GI . "Completed all changes up to current level!");
					break;
				default:
					$num = (int) $num;
					if($num !== 0){
						if($csession->hasLevelUnlocked($num)){
							$sender->showModal(new LevelsUi($num, $csession, false, ($sender->isSn3ak() || $sender->isTier3())));
							return;
						}
					}
					$sender->showModal(new ChallengeUi($sender));
					break;
			}
		}else{
			$sender->showModal(new ChallengeUi($sender));
		}
	}
}