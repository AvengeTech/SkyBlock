<?php namespace skyblock\combat\arenas\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\entity\Earth;
use skyblock\combat\arenas\entity\{
	SupplyDrop,
	MoneyBag
};

use core\utils\TextFormat;

class SpawnTest extends Command{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("skyblock.tier3");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player) return;

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RI . "Usage: /st <sd:mb>");
			return;
		}
		switch(array_shift($args)){
			case "sd":
				$box = new SupplyDrop($sender->getLocation());
				$box->spawnToAll();
				break;
			case "mb":
				$box = new MoneyBag($sender->getLocation(), null, (int) (array_shift($args) ?? 0), (int) (array_shift($args) ?? -1), (float) (array_shift($args) ?? 1));
				$box->spawnToAll();
				break;
			case "e":
				$box = new Earth($sender->getLocation(), null, (float) (array_shift($args) ?? 1));
				$box->spawnToAll();
				break;
		}

		$sender->sendMessage(TextFormat::GI . "Spawned thing!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}