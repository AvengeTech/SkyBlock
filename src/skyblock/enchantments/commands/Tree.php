<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pocketmine\item\{
    Hoe,
    Sword,
	Pickaxe
};

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\ItemData;
use skyblock\enchantments\uis\tree\SkillTreeUi;
use skyblock\fishing\item\FishingRod;

use core\utils\TextFormat;

class Tree extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$item = $sender->getInventory()->getItemInHand();
		if(
			!$item instanceof Pickaxe &&
			!$item instanceof Sword &&
			!$item instanceof FishingRod &&
			!$item instanceof Hoe
		){
			$sender->sendMessage(TextFormat::RI . "Only pickaxes, hoes, swords and fishing rods have skill trees! Make sure you're holding the correct item");
			return;
		}

		if(count($args) !== 0 && $sender->getRankHierarchy() >= Rank::HIERARCHY_HEAD_MOD){
			switch(array_shift($args)){
				case "as":
					$data = new ItemData($item);
					$data->addSkillPoint();
					$data->getItem()->setLore($data->calculateLores());
					$data->send($sender);
					$sender->sendMessage(TextFormat::GI . "Added skill point to held item!");
					return;
				case "al":
					$data = new ItemData($item);
					$data->levelUp();
					$data->sendLevelUpTitle($sender);
					$data->send($sender);
					$sender->sendMessage(TextFormat::GI . "Added level to held item!");
					return;
			}
		}
		$sender->showModal(new SkillTreeUi($sender, $item));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}