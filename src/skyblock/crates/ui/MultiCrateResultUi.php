<?php

namespace skyblock\crates\ui;

use core\AtPlayer;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use skyblock\crates\entity\Crate;
use skyblock\SkyBlockPlayer;

class MultiCrateResultUi extends CustomForm {

	public function __construct(string $title, array $lines, private Crate $crate, private bool $fromRemote = false) {
		parent::__construct($title);

		foreach ($lines as $line) {
			$this->addElement(new Label($line));
		}
	}

	public function handle($response, AtPlayer $player) {
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession();
		$keys = $session->getCrates()->getKeys($this->crate->getType());
		if ($keys <= 0) {
			$player->sendMessage($this->crate->getNoKeyMessage());
			if ($this->fromRemote) {
				$player->showModal(new RemoteCratesUi($player));
			}
			return;
		}
		$player->showModal(new OpenCrateUi($player, $this->crate, $this->fromRemote));
	}
}
