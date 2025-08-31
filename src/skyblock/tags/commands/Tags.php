<?php namespace skyblock\tags\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};
use skyblock\tags\uis\TagSelector;

use core\utils\TextFormat;

class Tags extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["tag", "t"]);
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) === 0){
			$sender->showModal(new TagSelector($sender));
			return;
		}
		$name = strtolower(array_shift($args));
		$tag = SkyBlock::getInstance()->getTags()->getTag($name);
		if($tag === null){
			$sender->sendMessage(TextFormat::RI . "Tag by that name doesn't exist!");
			return;
		}
		if(!($ts = $sender->getGameSession()->getTags())->hasTag($tag)){
			$sender->sendMessage(TextFormat::RI . "You do not have this tag unlocked!");
			return;
		}
		$ts->setActiveTag($tag);
		$sender->sendMessage(TextFormat::GI . "Tag has been equipped!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}