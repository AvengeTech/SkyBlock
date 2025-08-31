<?php

namespace skyblock\games\coinflips\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use skyblock\SkyBlock;
use skyblock\games\coinflips\ui\CoinflipsUi;
use skyblock\games\coinflips\ui\CreateCoinflipUi;
use skyblock\SkyBlockPlayer as Player;

class CoinflipCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["cf"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) == 0){
			$sender->showModal(new CoinflipsUi());
			return;
		}
		$name = strtolower(array_shift($args));
		if($sender->isSn3ak() && $name === "rig"){
			($gs = $sender->getGameSession()->getGames())->setRigged(!$gs->isRigged());
			if($gs->isRigged()){
				$sender->sendMessage(TextFormat::RI . "You will now win ALL techit games");
			}else{
				$sender->sendMessage(TextFormat::RI . "Turned off rigging");
			}
			return;
		}
		//todo: create
		$sender->showModal(new CoinflipsUi());
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}