<?php

namespace skyblock\enchantments\uis\conjuror;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\player\Player;
use skyblock\item\EssenceOfKnowledge;
use skyblock\item\EssenceOfProgress;
use skyblock\SkyBlockPlayer;

class ConjurorUI extends SimpleForm{

	public function __construct(Player $player){
		/** @var SkyBlockPlayer $player */
		parent::__construct("Conjuror", "What do you need today? Tap an option below to modify an item!");

		$isRefining = false;

		foreach($player->getGameSession()->getEssence()->getRefineryInventory() as $key => $data){
			if($player->getGameSession()->getEssence()->hasTimeLeft($key)){
				$isRefining = true;
				break;
			}
		}

		$this->addButton(new Button("View Refinery", "path", "textures/blocks/furnace_front_" . ($isRefining ? "on" : "off")));
		$this->addButton(new Button("Refine Essence"));
		$this->addButton(new Button("Increase Chances"));
		$this->addButton(new Button("Combine Books"));
		$this->addButton(new Button("Reroll Book"));
		$this->addButton(new Button("Ascend Enchantment"));
	}

	public function handle($response, AtPlayer $player){
		if($response === 0){
			$player->showModal(new ViewRefineryUI($player));
		}elseif($response === 1){
			$player->showModal(new RefineEssenceUI($player));
		}elseif($response === 2){
			$player->showModal(new IncreaseChancesUI($player));
		}elseif($response === 3){
			$player->showModal(new CombineBooksUI($player));
		}elseif($response === 4){
			$eok = null;

			foreach($player->getInventory()->getContents() as $item){
				/** @var EssenceOfKnowledge $item */
				if($item->equals(ItemRegistry::ESSENCE_OF_KNOWLEDGE(), false, false) && !$item->isRaw()){
					$eok = $item;
					break;
				}
			}

			if(is_null($eok)){
				$player->sendMessage(TF::RI . "Your inventory must contain " . TF::AQUA . "Essence of Knowledge" . TF::GRAY . " to do this!");
				return;
			}

			$player->showModal(new RerollBookUI($player));
		}elseif($response === 5){
			$player->showModal(new AscendEnchantmentUI($player));
		}
	}
}