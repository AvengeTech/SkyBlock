<?php

namespace skyblock\pets\command;

use core\AtPlayer;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use core\command\type\CoreCommand;
use core\rank\Rank;
use skyblock\pets\Structure;
use skyblock\pets\types\IslandPet;
use skyblock\pets\types\PetData;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class SpawnPet extends CoreCommand {

	public function __construct(private SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(!isset(($args[0])) || !isset(Structure::PETS[($id = $args[0])])){
			$pets = [];
			foreach (Structure::PETS as $id => $data) $pets[] = $data["name"] . "(ID: " . $id . ")";
			$list = implode(TF::AQUA . "\n - " . TF::GRAY, $pets);
			$sender->sendMessage(TF::RED . "Pet not found\n" . TF::AQUA . " - " . TF::GRAY . $list);
			return false;
		}
		$egg = ItemRegistry::PET_EGG()->setup($id)->init();
		if($sender->getInventory()->canAddItem($egg)) $sender->getInventory()->addItem($egg);
		return true;
	}

	public function getPlugin() : SkyBlock{
		return $this->plugin;
	}
}