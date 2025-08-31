<?php

namespace skyblock\combat\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;

class PvP extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["combat"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$ps = $sender->getGameSession()->getParkour();
		if ($ps->hasCourseAttempt()) {
			$sender->sendMessage(TextFormat::RI . "Cannot enter PvP mode during a parkour attempt");
			return;
		}
		$session = $sender->getGameSession()->getCombat();
		if ($session->inPvPMode()) {
			$sender->sendMessage(TextFormat::RI . "PvP is now disabled!");
		} else {
			$sender->sendMessage(TextFormat::RI . "PvP is now enabled!");
		}
		$session->togglePvPMode();
	}
}