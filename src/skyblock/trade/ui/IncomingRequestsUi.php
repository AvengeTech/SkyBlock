<?php namespace skyblock\trade\ui;

use pocketmine\player\Player;

use skyblock\trade\TradeComponent;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class IncomingRequestsUi extends SimpleForm{

	public array $requests;

	public function __construct(public TradeComponent $session){
		parent::__construct("Incoming Requests", "Tap one to manage.");

		$this->session = $session;

		foreach(($requests = array_values($session->getIncomingRequests())) as $request){
			$this->addButton(new Button("From: " . $request->getFrom()->getName() . "\n" . "Tap to manage"));
		}
		$this->addButton(new Button("Go back"));

		$this->requests = $requests;
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$session = $this->session;
		foreach($this->requests as $key => $request){
			if($key == $response){
				if($request->closed){
					$player->sendMessage(TextFormat::RI . "This request has expired.");
					return;
				}
				$player->showModal(new ManageIncomingRequestUi($request));
				return;
			}
		}

		$player->showModal(new TradeUi($player));
	}

}