<?php namespace skyblock\crates\ui;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\crates\entity\Crate;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\utils\TextFormat;
use core\network\Links;
use pocketmine\item\VanillaItems;

class OpenCrateUi extends SimpleForm{

	public function __construct(Player $player, public Crate $crate, private bool $fromRemote = false) {
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getCrates();
		parent::__construct("Open " . $crate->getType() . " crate", "Are you sure you want to open this? You have " . $session->getKeys($crate->getType()) . " " . $crate->getType() . " keys available.");

		$this->addButton(new Button("Open crate"));
		if (!$fromRemote) $this->addButton(new Button("Open crate (Fast Mode)"));
		$this->addButton(new Button("Open multiple crates"));
		if ($fromRemote) $this->addButton(new Button("Back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$crate = $this->crate;
		$session = $player->getGameSession()->getCrates();
		if($session->getKeys($crate->getType()) <= 0){
			$player->sendMessage($crate->getNoKeyMessage());
			return;
		}
		if($session->isOpening()){
			$player->sendMessage(TextFormat::RI . "You can only open one crate at a time!");
			return;
		}
		if ($this->fromRemote) {
			if ($response == 0) {
				$crate->openFast($player);
				return;
			}
			if ($response == 1) {
				$player->showModal(new OpenMultipleUi($player, $crate, $this->fromRemote));
				return;
			}
			if ($response == 2) {
				$player->showModal(new RemoteCratesUi($player));
				return;
			}
		} else {
			if ($response == 0 || $response == 1) {
				if ($crate->getTickType() !== 0) {
					$player->sendMessage(TextFormat::RI . "This crate is being used.");
					return;
				}
			}

			if (!$player->getInventory()->canAddItem(VanillaItems::AIR())) {
				$player->sendMessage(TextFormat::RI . "Your inventory is full! Please empty your inventory before using this.");
				return;
			}

			if ($response == 0) {
				$crate->start($player);
				return;
			}
			if ($response == 1) {
				if ($player->getRank() === "default" && !$session->hasInstantOpen()) {
					$player->sendMessage(TextFormat::RI . "You must have a rank or vote to open crates in Fast Mode! Purchase a rank at " . Links::SHOP . TextFormat::GRAY . ", or learn how to vote with " . TextFormat::YELLOW . "/vote");
					return;
				}
				$crate->openFast($player);
				return;
			}
			if ($response == 2) {
				if ($player->getRankHierarchy() < 2) {
					$player->sendMessage(TextFormat::RI . "You must be at least BLAZE rank to open multiple crates! Purchase one at " . Links::SHOP);
					return;
				}

				$player->showModal(new OpenMultipleUi($player, $crate, $this->fromRemote));
			}
		}
	}

}