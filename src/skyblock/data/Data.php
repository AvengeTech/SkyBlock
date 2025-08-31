<?php namespace skyblock\data;

use skyblock\SkyBlock;
use skyblock\data\commands\AddXp;
use skyblock\data\commands\SetXp;

class Data{

	public function __construct(public SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->register("addxp", new AddXp($plugin, "addxp", "Give player's XP Levels"));
		$plugin->getServer()->getCommandMap()->register("setxp", new SetXp($plugin, "setxp", "Set player's XP Levels"));
		//$plugin->getServer()->getCommandMap()->register("xpnote", new XpNoteCommand($plugin, "xpnote", "Put XP Levels into item form!"));
		//ItemFactory::getInstance()->register(new XpNote(), true);
	}

}