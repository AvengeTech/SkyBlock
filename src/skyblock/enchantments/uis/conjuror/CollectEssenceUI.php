<?php

namespace skyblock\enchantments\uis\conjuror;

use core\AtPlayer;
use core\ui\windows\ModalWindow;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use skyblock\SkyBlockPlayer;

class CollectEssenceUI extends ModalWindow{

	public function __construct(
		private int $key
	){
		parent::__construct(
			"Collect Essence", 
			"Your essence has been refined and is ready to collect",
			"Collect Essence",
			"Go Back"
		);
	}

	public function handle($response, AtPlayer $player){
		if($response){
			/** @var SkyBlockPlayer $player */
			$session = $player->getGameSession()->getEssence();
			$data = explode(":", $session->getFromInventory($this->key));

			$essenceType = $data[0];
			$essenceRarity = $data[1] ?? 1;

			$item = (match($essenceType){
				"s" => ItemRegistry::ESSENCE_OF_SUCCESS()->setup($essenceRarity, -1, -1, false),
				"k" => ItemRegistry::ESSENCE_OF_KNOWLEDGE()->setup($essenceRarity, -1, false),
				"a" => ItemRegistry::ESSENCE_OF_ASCENSION()->setup($essenceRarity, false)
			})->init();

			$session->removeFromInventory($this->key);
			$player->getInventory()->addItem($item);
			$player->sendMessage(TF::GN . "You have collected your refined essence from the refinery");
		}else{
			$player->showModal(new ViewRefineryUI($player));
		}
	}
}