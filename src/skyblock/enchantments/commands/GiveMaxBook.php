<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\ItemRegistry;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\item\MaxBook;

use core\utils\TextFormat;

class GiveMaxBook extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) < 1){
			$sender->sendMessage(TextFormat::RN . "Usage: /givemaxbook <1(common):2(uncommon):3(rare):4(legendary):5(divine)>");
			return;
		}

		$type = (int) array_shift($args);
		if($type <= 0 || $type > 5){
			$sender->sendMessage(TextFormat::RN . "Usage: /givemaxbook <1(common):2(uncommon):3(rare):4(legendary):5(divine)>");
			return;
		}

		$book = ItemRegistry::MAX_BOOK();
		$book->init($type);
		$sender->getInventory()->addItem($book);

		$sender->sendMessage(TextFormat::GI . "You were given Max book!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}