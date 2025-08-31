<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\entity\ArmorStand;

class ClearArmorStands extends CoreCommand {

	public function __construct(public SkyBlock $plugin, $name, $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$count = 0;
		foreach($sender->getPosition()->getWorld()->getEntities() as $entity){
			if($entity instanceof ArmorStand){
				$entity->despawnFromAll();
				$count++;
			}
		}
		$sender->sendMessage(TextFormat::GN . "Cleared " . $count . " armor stands");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}