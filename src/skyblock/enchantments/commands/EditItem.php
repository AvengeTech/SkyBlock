<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\enchanter\StaffItemEditorUi;

use core\utils\TextFormat;

class EditItem extends CoreCommand {

	public function __construct(public \skyblock\SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
		$this->setAliases(["ei"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new StaffItemEditorUi($sender));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
