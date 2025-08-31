<?php

namespace skyblock\enchantments\uis\conjuror\confirm;

use core\AtPlayer;
use core\ui\windows\ModalWindow;
use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\event\RefineEssenceEvent;
use skyblock\enchantments\uis\conjuror\RefineEssenceUI;
use skyblock\item\Essence;
use skyblock\SkyBlockPlayer;

class ConfirmRefineEssenceUI extends ModalWindow{

	private int $price;

	public function __construct(
		private Essence $essence
	){
		$this->price = match($essence->getType()){
			"s" => 10 + (10 * $essence->getRarity()),
			"k" => 50,
			"a" => 40 + (10 * $essence->getRarity()) + ($essence === ED::RARITY_DIVINE ? 10 : 0)
		};
		$this->price *= $essence->getCount();

		parent::__construct(
			"Confirm Refine", 
			"The " . TF::AQUA ."x" . $this->essence->getCount() . " " . $this->essence->getCustomName() . TF::RESET . " will cost " . TF::DARK_AQUA . $this->price . " Essence" . TF::WHITE . ", are you sure you want to refine this essence?",
			"Refine Essence",
			"Go Back"
		);
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
		if($response){
			$slot = $player->getInventory()->first($this->essence, true);
			if($slot == -1){
				$player->sendMessage(TF::RN . "Essence you're trying to refine no longer exists in inventory!");
				return;
			}

			if($player->getGameSession()->getEssence()->getEssence() < $this->price){
				$player->sendMessage(TF::RN . "You do not have enough essence to refine this!");
				return;
			}

			$rarity = $this->essence->getRarity();
			$minutes = (5 + ($rarity === ED::RARITY_DIVINE ? $rarity : $rarity - 1));
			$count = $this->essence->getCount();

			for($i = 0; $i < $count; $i++){
				$tempEssence = clone $this->essence;

				$player->getGameSession()->getEssence()->addToInventory($tempEssence, time() + ($minutes * 60)); // 10 mins for divine, 5 - 8mins for rest
				$this->essence->pop();

				$ev = new RefineEssenceEvent($player, $tempEssence);
				$ev->call();
			}

			$player->getInventory()->setItem($slot, $this->essence);
			$player->getGameSession()->getEssence()->subEssence($this->price);
			$player->sendMessage(TF::GI . "The essence is now refining, come back in " . TF::YELLOW . $minutes . TF::GRAY . " minutes to collect your refined essence!");
		}else{
			$player->showModal(new RefineEssenceUI($player));
		}
	}
}