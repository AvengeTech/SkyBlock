<?php

namespace skyblock\leaderboards\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;
use skyblock\leaderboards\ui\LeaderboardPrizesUi;

class Prizes extends CoreCommand {

	public function __construct(public SkyBlock $plugin, $name, $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->sendMessage(TextFormat::RI . "Leaderboard Prizes are currently disabled!");
		// $sender->showModal(new LeaderboardPrizesUi());
	}
}