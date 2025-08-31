<?php namespace skyblock\lms\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};

use core\utils\TextFormat;

class LmsCommand extends Command{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("skyblock.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!($sender instanceof Player)) return;

		$lms = SkyBlock::getInstance()->getLms();

		if(count($args) == 0){
			$sender->sendMessage(TextFormat::RI . "Usage: /lms tp");
			return;
		}

		$active = $lms->getActiveGames();

		$action = strtolower(array_shift($args));
		switch($action){
			case "teleport":
			case "go":
			case "goto":
			case "tp":
				if(count($active) === 0){
					$sender->sendMessage(TextFormat::RI . "No LMS event is active.");
					return;
				}
				$session = $sender->getGameSession()->getLms();
				/**if($session->hasCooldown()){
					$sender->sendMessage(TextFormat::RI . "You have recently won a KOTH match! You can participate in another one in " . TextFormat::WHITE . $session->getFormattedCooldown());
					return;
				}*/
				if(count($active) == 1){
					foreach($active as $game){
						if($game->tpCooldown($sender)){
							$sender->sendMessage(TextFormat::RI . "You are using this command too fast!");
							return;
						}
						$game->teleportTo($sender);
						return;
					}
					return;
				}
				$arena = strtolower(array_shift($args) ?? "");
				if($arena === null){
					$sender->sendMessage(TextFormat::RI . "Usage: /lms tp <name>");
					foreach($active as $game){
						$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $game->getName());
					}
					return;
				}
				foreach($active as $game){
					if(strtolower($game->getName()) == $arena){
						if($game->tpCooldown($sender)){
							$sender->sendMessage(TextFormat::RI . "You are using this command too fast!");
							return;
						}
						$game->teleportTo($sender);
						return;
					}
				}
				$sender->sendMessage(TextFormat::RI . "Invalid arena provided! /lms tp <arena>");
				foreach($active as $game){
					$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $game->getName());
				}
				return;
			case "start":
				if($sender instanceof Player){
					if(!$sender->isTier3()){
						$sender->sendMessage(TextFormat::RI . "No permission!");
						return;
					}
				}
				$arena = strtolower(array_shift($args));
				if($arena === null){
					$sender->sendMessage(TextFormat::RI . "Usage: /lms start <arena>");
					foreach($lms->getGames() as $match){
						$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $match->getName());
					}
					return;
				}
				$game = $lms->getGameByName($arena);
				if($game === null){
					$sender->sendMessage(TextFormat::RI . "Invalid arena provided! /lms start <arena>");
					foreach($lms->getGames() as $game){
						$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $game->getName());
					}
					return;
				}
				if($game->isActive()){
					$sender->sendMessage(TextFormat::RI . "This game is already active!");
					return;
				}
				$lms->startLms($game->getId());
				break;

			case "yuh":
				if($sender instanceof Player){
					if(!$sender->isTier3()){
						$sender->sendMessage(TextFormat::RI . "No permission!");
						return;
					}
				}
				$lms = $sender->getGameSession()->getLms();
				if(!$lms->inGame()){
					$sender->sendMessage(TextFormat::RI . "Can only use this while in a LMS arena");
					return;
				}
				$game = $lms->getGame();
				if($game === null){
					$sender->sendMessage(TextFormat::RI . "Can only use this while in a LMS arena");
					return;
				}
				$game->start();
				break;
			case "stop":
			case "end":
				if($sender instanceof Player){
					if(!$sender->isTier3()){
						$sender->sendMessage(TextFormat::RI . "No permission!");
						return;
					}
				}
				$arena = strtolower(array_shift($args) ?? "");
				if($arena === null){
					$sender->sendMessage(TextFormat::RI . "Usage: /lms end <arena>");
					foreach($lms->getActiveGames() as $game){
						$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $game->getName());
					}
					return;
				}
				$game = $lms->getGameByName($arena);
				if($game === null){
					$sender->sendMessage(TextFormat::RI . "Invalid arena provided! /lms start <arena>");
					foreach($lms->getGames() as $game){
						$sender->sendMessage(TextFormat::GRAY . "- " . TextFormat::AQUA . $game->getName());
					}
					return;
				}
				if(!$game->isActive()){
					$sender->sendMessage(TextFormat::RI . "This match is not active!");
					return;
				}
				$game->end(true);
				$sender->getServer()->broadcastMessage(TextFormat::GI . TextFormat::LIGHT_PURPLE . "LMS match " . TextFormat::YELLOW . $game->getName() . TextFormat::LIGHT_PURPLE . " has been force ended.");
				break;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}