<?php namespace skyblock\combat\arenas\commands;

use core\staff\anticheat\session\SessionManager;
use core\utils\conversion\LegacyItemIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\utils\TextFormat;

class ArenaCommand extends Command{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("skyblock.perm");
		$this->setAliases(["warzone", "wz"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var SkyBlockPlayer $sender */
		if(!$sender instanceof Player) return;

		$arenas = SkyBlock::getInstance()->getCombat()->getArenas();
		if($arenas->inArena($sender)){
			$sender->sendMessage(TextFormat::RI . "You are already in the warzone!");
			return;
		}

		$arena = $arenas->getArena();

		if($arena->isLocked()){
			$sender->sendMessage(TextFormat::RI . "Warzone is currently locked!");
			return;
		}

		if($sender->getArmorInventory()->getItem(1)->getTypeId() == LegacyItemIds::legacyIdToTypeId(444)){
			$sender->sendMessage(TextFormat::RI . "Elytras are not allowed in the warzone.");
			return;
		}

		$ksession = $sender->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}

		$arena->teleportTo($sender);
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}