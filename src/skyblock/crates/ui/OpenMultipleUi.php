<?php namespace skyblock\crates\ui;

use pocketmine\player\Player;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Slider
};
use core\utils\TextFormat;

use skyblock\crates\entity\Crate;
use skyblock\SkyBlockPlayer;

class OpenMultipleUI extends CustomForm{

	public bool $allowed = true;

	public function __construct(Player $player, public Crate $crate, private bool $fromRemote = false) {
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getCrates();
		$keys = $session->getKeys($crate->getType());

		parent::__construct("Use multiple keys");

		if ($keys <= 0) {
			$this->addElement(new Label(TextFormat::RI . "You do not have any " . $crate->getType() . " keys!"));
			$this->allowed = false;
			return;
		}

		$inventory = $player->getInventory();
		$empty = $inventory->getSize() - count($inventory->getContents());

		if ($empty <= 0) {
			$this->addElement(new Label(TextFormat::RI . "You do not have enough inventory space to open this crate!"));
			$this->allowed = false;
			return;
		}

		$this->addElement(new Label("Use this menu to open multiple Crates at once!" . "\n" . "\n" . "You have " . $keys . " " . $crate->getType() . " keys available and " . $empty . " inventory slots available."));

		$max = min($empty, $keys);

		$this->addElement(new Slider("How many keys?", 1, $max));
	}

	public function handle($response, Player $player){
		if (!$this->allowed) return;
		/** @var SkyBlockPlayer $player */
		$requested = (int) $response[1] ?? 0;
		$session = $player->getGameSession()->getCrates();
		$keys = $session->getKeys(($crate = $this->getCrate())->getType());
		if ($keys < $requested || $keys <= 0) {
			$player->sendMessage(TextFormat::RI . "You do not have enough keys to perform this action!");
			return;
		}
		$crate->openMultiple($player, $requested, $this->fromRemote);
	}

	public function getCrate() : Crate{
		return $this->crate;
	}

}