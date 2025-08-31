<?php namespace skyblock\utils;

use core\staff\anticheat\session\SessionManager;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\world\Position;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\islands\{
	IslandManager,
	Island
};

use core\utils\{
	LoadAction as CoreLoadAction,
	TextFormat
};

class LoadAction extends CoreLoadAction{

	public function process(bool $preLoad = false) : void{
		/** @var SkyBlockPlayer $player */
		$player = $this->getPlayer();
		$adata = $this->getActionData();
		if(!$player instanceof Player) return;
		switch($this->getAction()){
			case "arena":
				$arena = SkyBlock::getInstance()->getCombat()->getArenas()->getArena(/**$id*/);
				if($arena === null){
					$player->sendMessage(TextFormat::RI . "You tried teleporting to an invalid arena... weirdChamp");
					$player->gotoSpawn();
					return;
				}
				if($preLoad){
					$arena->teleportTo($player, false, true);
					return;
				}
				$id = $adata["id"] ?? "";

				$arena->teleportTo($player, true, false);
				break;

			case "crates":
				$player->teleport(new Position(-14695.5, 123, 13500.5, Server::getInstance()->getWorldManager()->getWorldByName("scifi1")), 135, 0);
				if(!$preLoad){
					$player->sendMessage(TextFormat::GN . "Teleported to crates!");
				}
				break;
				
			case "leaderboards":
				$player->teleport(new Position(-14695.5, 123, 13666.5, Server::getInstance()->getWorldManager()->getWorldByName("scifi1")), 45, 0);
				if(!$preLoad){
					$player->sendMessage(TextFormat::GN . "Teleported to leaderboards!");
				}
				break;

			case "island":
				if($preLoad) return;
				$worldName = $adata["world"];
				SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($player, $worldName);
				break;
			case "lastisland":
				if($preLoad) return;
				$island = $adata["island"];
				$im = SkyBlock::getInstance()->getIslands()->getIslandManager();
				if(($is = $im->getIslandBy($island)) !== null){
					$player->getGameSession()->getIslands()->setLastIslandAt($is);
				}else{
					$im->loadIsland($island, function(Island $theIsland) use($player) : void{
						if(!$player->isConnected()) return;
						$player->getGameSession()->getIslands()->setLastIslandAt($theIsland);
					}, function(int $error) use($player, $island) : void{
						if(!$player->isConnected()) return;
						switch($error){
							case IslandManager::ERROR_ALREADY_LOADED:
								$player->getGameSession()->getIslands()->setLastIslandAt(SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($island));
								break;
						}
					}, false);
				}
				break;
				
			case "koth":
				$gameId = $adata["gameId"];
				$game = SkyBlock::getInstance()->getKoth()->getGameById($gameId);
				if(!$game->isActive()){
					$player->sendMessage(TextFormat::RI . "The KOTH match you are trying to teleport to is no longer active.");
					$player->gotoSpawn();
					return;
				}

				if($preLoad){
					$game->teleportTo($player, false, true);
					return;
				}

				$game->teleportTo($player, true, false);
				break;

			case "lms":
				$gameId = $adata["gameId"];
				$game = SkyBlock::getInstance()->getLms()->getGameById($gameId);
				if(!$game->isActive()){
					$player->sendMessage(TextFormat::RI . "The LMS match you are trying to teleport to is no longer active.");
					$player->gotoSpawn();
					return;
				}

				if($preLoad){
					$game->teleportTo($player, false, true);
					return;
				}

				$game->teleportTo($player, true, false);
				break;

			default:
				parent::process($preLoad);
				break;
		}
	}

}