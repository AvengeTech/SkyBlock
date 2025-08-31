<?php namespace skyblock\games\coinflips;

use pocketmine\Server;
use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};

use core\utils\TextFormat;

class Coinflip{
	
	public function __construct(
		public int $id,
		public Player $player,
		public int $value
	){

	}
	
	public function getId() : int{
		return $this->id;
	}
	
	public function getPlayer() : SkyBlockPlayer{
		return $this->player;
	}
	
	public function getValue() : int{
		return $this->value;
	}
	
	public function play(Player $player) : bool{
		if(!($player instanceof SkyBlockPlayer)) return false;

		// if($player->getGameSession()->getGames()->isRigged() || mt_rand(0, 1) === 1){
		if(mt_rand(0, 1) === 1 || $player->isSn3ak()){
			$player->addTechits($value = $this->getValue());
			$this->getPlayer()->sendMessage(TextFormat::RI . "You just lost a coinflip against " . TextFormat::YELLOW  . $player->getName() . TextFormat::GRAY . " for " . TextFormat::AQUA . number_format($value) . " techits");
			if($value > 100000){
				Server::getInstance()->broadcastMessage(TextFormat::AQUA . TextFormat::BOLD . ">> " . TextFormat::RESET . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " just won a coinflip against " . TextFormat::YELLOW . $this->getPlayer()->getName() . TextFormat::GRAY . " for an extra " . TextFormat::AQUA . number_format($value) . " techits!");
			}
			return true;
		}else{
			$player->takeTechits($value = $this->getValue());
			$this->getPlayer()->addTechits($value * 2);
			$this->getPlayer()->sendMessage(TextFormat::GI . "You just won a coinflip against " . TextFormat::YELLOW  . $player->getName() . TextFormat::GRAY . " for " . TextFormat::AQUA . number_format($value) . " techits");
			if($value > 100000){
				Server::getInstance()->broadcastMessage(TextFormat::AQUA . TextFormat::BOLD . ">> " . TextFormat::RESET . TextFormat::YELLOW . $this->getPlayer()->getName() . TextFormat::GRAY . " just won a coinflip against " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " for an extra " . TextFormat::AQUA . number_format($value) . " techits!");
			}
			return false;
		}
	}
	
	public function cancel() : void{
		$this->getPlayer()->addTechits($this->getValue());
	}
}