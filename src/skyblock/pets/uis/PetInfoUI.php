<?php

namespace skyblock\pets\uis;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;
use pocketmine\player\Player;
use skyblock\pets\types\EntityPet;
use skyblock\SkyBlockPlayer;

class PetInfoUI extends SimpleForm{

	public function __construct(
		private Player $player, 
		private EntityPet $pet
	){
		$title = TF::BOLD . TF::RED . ($player->getXuid() == $pet->getOwner()?->getXuid() ? "Your" : $pet->getOwner()?->getName() . "'s") . " Pet";
		$data = $pet->getPetData();

		$content = TF::GRAY . ($player->getXuid() == $pet->getOwner()?->getXuid() ? "Your" : $pet->getOwner()?->getName() . "'s") . " pet is a loser\n\n";
		$content .= TF::GRAY . "Name: " . TF::GOLD . $data->getName() . TF::RESET . "\n";
		$content .= TF::GRAY . "Level: " . TF::AQUA . $data->getLevel() . "\n";
		$content .= TF::GRAY . "Xp: " . TF::BLUE . $data->getXp() . "/" . $data->getRequiredXp() . "\n";
		$content .= TF::GRAY . "Energy: " . TF::YELLOW . $data->getEnergy() . "/" . round($data->getMaxEnergy(), 2) . "\n";
		$content .= TF::GRAY . "Description:\n";
		$content .= TF::GRAY . TF::WHITE . str_replace("%", "%%%", $data->getDescription());

		parent::__construct($title, $content);

		if($player->getXuid() == $this->pet->getOwner()?->getXuid()) $this->addButton(new Button(TF::GOLD . "Rest"));
		$this->addButton(new Button(TF::RED . "Close"));
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
		$session = $player->getGameSession()->getPets();

		if($player->getXuid() !== $this->pet->getOwner()?->getXuid()) return;

		if($response === 0){
			$session->setActivePet(null);
			
			$this->pet->getPetData()->rest(true);
			
			if(!($this->pet->isFlaggedForDespawn() || $this->pet->isClosed())){
				$this->pet->flagForDespawn();
			}
			return;
		}
	}
}