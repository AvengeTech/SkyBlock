<?php namespace skyblock\games\coinflips\ui;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock, 
	SkyBlockPlayer
};

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class CoinflipsUi extends SimpleForm{

	public array $coinflips = [];

	public function __construct(string $message = "", bool $error = true){
		parent::__construct("Coinflips",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::RESET : "") .
			"Select a coinflip below for a chance to win!"
		);
		$this->addButton(new Button("Create Coinflip"));
		foreach(SkyBlock::getInstance()->getGames()->getCoinflips()->getGames() as $game){
			$this->addButton(new Button(TextFormat::AQUA . number_format($game->getValue()) . " techits" . PHP_EOL . TextFormat::GOLD . $game->getPlayer()->getName()));
			$this->coinflips[] = $game;
		}
	}

	public function handle($response, Player $player){
		if(!($player instanceof SkyBlockPlayer)) return;
		if($response === 0){
			$player->showModal(new CreateCoinflipUi($player));
			return;
		}
		$flip = $this->coinflips[$response - 1] ?? null;
		if($flip === null) return;
		$cf = SkyBlock::getInstance()->getGames()->getCoinflips();
		if($cf->getGame($flip->getId()) === null || !$flip->getPlayer()->isConnected()){
			$player->showModal(new CoinflipsUi("That coinflip no longer exists!"));
			return;
		}
		if($flip->getPlayer() === $player){
			$player->showModal(new MyCoinflipUi($flip));
			return;
		}
		if($player->getTechits() < $flip->getValue()){
			$player->showModal(new CoinflipsUi($player, "You don't have enough techits!"));
			return;
		}
		$player->showModal(new CoinflipConfirmUi($flip));
	}

}