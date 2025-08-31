<?php namespace skyblock\games\ui;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\games\coinflips\ui\CoinflipsUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class GamesUi extends SimpleForm{

	public function __construct(string $message = "", bool $error = true){
		parent::__construct("Techit Games",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::RESET : "") .
			"Select a game you'd like to play"
		);
		$this->addButton(new Button("Coinflips" . PHP_EOL . "(" . count(SkyBlock::getInstance()->getGames()->getCoinflips()->getGames()) . " ongoing)"));
		$this->addButton(new Button("Rock, Paper, Scissors" . PHP_EOL . "COMING SOON(tm)"));//"(" . count(SkyBlock::getInstance()->getGames()->getRps()->getGames()) . " ongoing)"));
	}

	public function handle($response, Player $player){
		if(!($player instanceof SkyBlockPlayer)) return;
		
		if($response === 0){
			$player->showModal(new CoinflipsUi());
			return;
		}
		if($response === 1){
			$player->sendMessage(TextFormat::RI . "Coming soon.... To an AvengeTech near you");
			return;
		}
	}

}