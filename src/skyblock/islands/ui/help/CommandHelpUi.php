<?php namespace skyblock\islands\ui\help;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class CommandHelpUi extends SimpleForm{

	const HELP = [
		"go" => [
			"arguments" => [],
			"alias" => ["home"],
			"description" => "Teleports you to your default island (configurable in /settings)"
		],
		"chat" => [
			"arguments" => [],
			"alias" => ["c"],
			"description" => "Puts you in island chat mode"
		],
		"menu" => [
			"arguments" => [],
			"alias" => ["m"],
			"description" => "Opens menu for the island you're at"
		],

		"spawn" => [
			"arguments" => [],
			"alias" => [],
			"description" => "Teleport to your current island's spawn"
		],
		"setspawn" => [
			"arguments" => [],
			"alias" => [],
			"description" => "Sets the spawn at your island"
		],
		
		"warp" => [
			"arguments" => [],
			"alias" => ["warps"],
			"description" => "View your current island's warps"
		],

		"movemenu" => [
			"arguments" => [],
			"alias" => ["move"],
			"description" => "Move your current island's island menu NPC"
		],
		"invite" => [
			"arguments" => [],
			"alias" => ["inv"],
			"description" => "Invite a new player to your current island"
		],

		"invites" => [
			"arguments" => [],
			"alias" => [],
			"description" => "View received island invites"
		],
		"signshop" => [
			"arguments" => [],
			"alias" => ["ss"],
			"description" => "Puts you in island sign shop mode (to setup/modify sign shops)"
		],
		"text" => [
			"arguments" => [],
			"alias" => ["ft"],
			"description" => "View/edit island floating texts"
		],
		"tutorial" => [
			"arguments" => [],
			"alias" => ["tut"],
			"description" => "Visit the tutorial island"
		],

		"stp" => [
			"arguments" => [],
			"alias" => [],
			"description" => "Teleport to any island (staff)",
			"staff" => true
		],

		"help" => [
			"arguments" => [],
			"alias" => [],
			"description" => "View all gang commands and information about them"
		],

	];

	public function __construct(Player $player){
		/** @var SkyBlockPlayer $player */
		parent::__construct("Islands Help Page",
			"Select an option below to learn more!"
		);
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::AQUA . "TUTORIAL ISLAND"));
		foreach(self::HELP as $name => $data){
			if((!($data["staff"] ?? false)) || $player->isStaff()) $this->addButton(new Button(TextFormat::GOLD . "/island " . $name));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response == 0){
			SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($player, Island::TUTORIAL);
			return;
		}
		$key = 0;
		foreach(self::HELP as $name => $entry){
			if($response - 1 == $key){
				$player->showModal(new CommandInfoUi($name, $entry));
				return;
			}
			$key++;
		}
	}

}