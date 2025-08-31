<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\item\{
	Nametag,
	CustomDeathTag,
	EnchantmentRemover
};

use core\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\scheduler\ClosureTask;
use skyblock\item\inventory\SpecialItemsInventory;

class GiveSpecial extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(!is_null($sender->getCurrentWindow())) $sender->removeCurrentWindow();

		$sender->getNetworkSession()->getInvManager()->getContainerOpenCallbacks()->add(function(int $id, Inventory $inventory) : array{
			return []; //trollface
		});
		$sender->setCurrentWindow(new SpecialItemsInventory);
		return;

		if(count($args) < 1){
			$sender->sendMessage(TextFormat::RN . "Usage: /givespecial <nt:cdt:er> [extra args]");
			return;
		}

		$type = array_shift($args);
		switch($type){
			case "nametag":
			case "nt":
				$item = ItemRegistry::NAMETAG();
				$item->init();
				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given nametag!");
				break;
			case "cdt":
				$item = ItemRegistry::CUSTOM_DEATH_TAG();
				$item->init();
				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given custom death tag!");
				break;
			case "er":
				$item = ItemRegistry::ENCHANTMENT_REMOVER();
				$item->init((int) (array_shift($args) ?? -1));
				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given enchantment remover!");
				break;
			case "rb":
				$item = ItemRegistry::MAX_BOOK();
				$item->setup(2, -1, true);
				$item->init();
				$item->setCount((int) (array_shift($args) ?? 1));

				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given random book!");
				break;
			case "mb":
				$item = ItemRegistry::MAX_BOOK();
				$item->setup(1, min(5, max(1, (int) array_shift($args))));
				$item->init();
				$item->setCount((int) (array_shift($args) ?? 1));
				
				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given max book!");
				break;
			case "spawner":
			case "sp":
				// $item = BlockRegistry::MOB_SPAWNER();
				// $item->setLevel(min(8, max(1, (int) array_shift($args))));
				// $item->init();
				// $item->setCount((int) (array_shift($args) ?? 1));
				
				// $sender->getInventory()->addItem($item);
				// $sender->sendMessage(TextFormat::GI . "Given spawner!");
				break;
			case "sellwand":
			case "sw":
				$item = ItemRegistry::SELL_WAND();
				$item->init();
				$item->setCount((int) (array_shift($args) ?? 1));

				$sender->getInventory()->addItem($item);
				$sender->sendMessage(TextFormat::GI . "Given sellwand!");
				break;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}