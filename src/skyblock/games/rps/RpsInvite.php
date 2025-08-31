<?php namespace skyblock\games\rps;

use pocketmine\player\Player;

use core\utils\TextFormat;

class RpsInvite{
	
	const INVITE_TIMER = 60;
	
	public function __construct(
		public Player $inviting,
		public Player $invited,
		public int $created
	){
		
	}
	
	public function tick() : bool{
		return $this->getCreated() > time() + self::INVITE_TIMER;
	}
	
	public function getInviting() : Player{
		return $this->inviting;
	}
	
	public function getInvited() : Player{
		return $this->invited;
	}
	
	public function getCreated() : int{
		return $this->created;
	}
	
	public function accept() : void{
		
	}
	
	public function decline(string $reason = "Expired") : void{
		if(($player = $this->getInviting())->isConnected()){
			$player->sendMessage(TextFormat::RI . "Your RPS request to " . TextFormat::YELLOW . $this->getInvited()->getName() . TextFormat::GRAY . " was declined!" . ($reason !== "" ? " (Reason: " . $reason . ")" : ""));
		}
	}
	
}