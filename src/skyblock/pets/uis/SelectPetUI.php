<?php

namespace skyblock\pets\uis;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\player\Player;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\Structure;
use skyblock\pets\types\IslandPet;
use skyblock\pets\types\PetData;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class SelectPetUI extends SimpleForm{

	public function __construct(
		private Player $player, 
		private PetData $data,
		string $label = ""
	){
		if($data->isResting()) $data->updateRestEnergy();

		$title = TF::BOLD . ED::rarityColor($data->getRarity()) . $data->getDefaultName() . " Pet";

		$label = TF::GRAY . "Your pet is a loser\n\n";
		$label .= TF::GRAY . "Name: " . TF::GOLD . $data->getName() . TF::RESET . "\n";
		$label .= TF::GRAY . "Level: " . TF::AQUA . $data->getLevel() . "\n";
		$label .= TF::GRAY . "Xp: " . TF::BLUE . $data->getXp() . "/" . $data->getRequiredXp() . "\n";
		$label .= TF::GRAY . "Energy: " . TF::YELLOW . $data->getEnergy() . "/" . $data->getMaxEnergy() . "\n";
		$label .= TF::GRAY . "Description:\n";
		$label .= TF::GRAY . TF::WHITE . str_replace("%", "%%%", $data->getDescription());

		parent::__construct($title, $label);

		$this->addButton(new Button(TF::DARK_YELLOW . "Rename"));
		$this->addButton(new Button(($data->isResting() ? TF::GREEN . "Wake Up" : TF::GOLD . "Rest")));
		$this->addButton(new Button(TF::DARK_PURPLE . "Remove"));
		$this->addButton(new Button(TF::RED . "Back"));
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
		$session = $player->getGameSession()->getPets();
		$data = $this->data;

		switch($response){
			case 0:
				$player->showModal(new PetRenameUI($data));
				break;

			case 1:
				if($data->isResting()){
					if(!is_null($session->getActivePet())){
						$player->showModal(new self($player, $data, TF::RED . "You could only have one active pet.\n\n" . TF::RESET));
						return; 
					}
		
					if((($data->getEnergy() / $data->getMaxEnergy()) * 100) < 15){
						$player->showModal(new self($player, $data, TF::RED . "Your pet must have more than 15%%% of energy.\n\n" . TF::RESET));
						return;
					}

					if(
						SkyBlock::getInstance()->getCombat()->getArenas()->inArena($player) ||
						SkyBlock::getInstance()->getKoth()->inGame($player) ||
						SkyBlock::getInstance()->getLms()->inGame($player)
					){
						$player->showModal(new self($player, $data, TF::RED . "You must be out of a combat zone to wake up pet!"));
						return;
					}
		
					$class = Structure::PETS[$data->getIdentifier()][Structure::DATA_CLASS];
					/** @var IslandPet $pet */
					$pet = new $class($player->getLocation());
					$pet->setOwner($player);
					$pet->setPetData($data);
					$pet->updateNameTag();
					$pet->spawnToAll();
		
					$session->setActivePet($pet);
				}else{
					$data->rest(true);
					
					$active = $session->getActivePet();
					
					if(!($active->isFlaggedForDespawn() || $active->isClosed())){
						$active->flagForDespawn();
					}
		
					$session->setActivePet(null);
					$player->showModal(new self($player, $data, TF::GOLD . "You have set your " . ED::rarityColor($data->getIdentifier()) . $data->getDefaultName() . " Pet " . TF::GOLD . "to rest.\n\n" . TF::RESET));
				}
				break;

			case 2:
				if($session->isActivePet($data->getIdentifier())){
					$player->showModal(new self($player, $data, TF::RED . "You can't not remove this pet from your pets list as it is your current active pet.\n\n"));
					return;
				}

				$item = ItemRegistry::PET_EGG()->setup($data->getIdentifier(), $data)->init();

				if(!$player->getInventory()->canAddItem($item)){
					$player->showModal(new self($player, $data, TF::RED . "You can't not remove this pet from your pets list, you do not have space in your inventory.\n\n"));
					return;
				}

				$session->removePet($data->getIdentifier());
				$player->getInventory()->addItem($item);

				$player->sendMessage(TF::GREEN . "Removed pet from your pets lists.");
				break;

			case 3:
				$player->showModal(new MyPetsUI($player));
				break;
		}
	}
}