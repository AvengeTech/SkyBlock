<?php

namespace skyblock\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\player\{
	Player,
	GameMode
};
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use pocketmine\network\mcpe\protocol\types\Enchant;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\type\ToggledArmorEnchantment;

class SpawnCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if($sender instanceof Player && (empty($args) || !$sender->isTier3())){
			$isession = $sender->getGameSession()->getIslands();
			if($isession->atIsland()){
				$isession->setIslandAt(null);
			}

			$ps = $sender->getGameSession()->getParkour();
			if($ps->hasCourseAttempt()){
				$ps->getCourseAttempt()->removeScoreboard();
				$ps->setCourseAttempt();
			}

			foreach($sender->getArmorInventory()->getContents() as $armor){
				ToggledArmorEnchantment::onToggle($sender, $armor, $armor);
			}

			$ksession = $sender->getGameSession()->getKoth();
			if($ksession->inGame()){
				$ksession->setGame();
			}

			$lsession = $sender->getGameSession()->getLms();
			if($lsession->inGame()){
				$lsession->setGame();
			}

			$sender->gotoSpawn();
			if($sender->getGamemode() === GameMode::SURVIVAL()){
				$sender->setGamemode(GameMode::ADVENTURE());
			}
			$sender->setAllowFlight(true);
			$sender->sendMessage(TextFormat::GN . "Teleported to spawn!");
			return;
		}
		/** @var SkyBlockPlayer $player */
		$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
		if(!$player instanceof Player){
			$sender->sendMessage(TextFormat::RI . "Player not online!");
			return;
		}

		$isession = $player->getGameSession()->getIslands();
		if($isession->atIsland()){
			$isession->setIslandAt(null);
		}

		$ps = $player->getGameSession()->getParkour();
		if($ps->hasCourseAttempt()){
			$ps->getCourseAttempt()->removeScoreboard();
			$ps->setCourseAttempt();
		}

		foreach($player->getArmorInventory()->getContents() as $armor){
			ToggledArmorEnchantment::onToggle($player, $armor, $armor);
		}

		$ksession = $player->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}

		$player->gotoSpawn();
		if($player->getGamemode() === GameMode::SURVIVAL()){
			$player->setGamemode(GameMode::ADVENTURE());
		}
		$player->setAllowFlight(true);
		$sender->sendMessage(TextFormat::GN . "Teleported " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " to spawn!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}