<?php namespace skyblock\games\rps;

use pocketmine\player\Player;

class Rps{

	public static int $gameId = 0;

	public array $games = [];
	public array $invites = [];

	public function __construct(){

	}

	public function tick() : void{
		foreach($this->getGames() as $game){
			$game->tick();
		}
		foreach($this->getInvites() as $key => $invite){
			if(!$invite->tick()){
				$invite->decline();
				unset($this->invites[$key]);
			}
		}
	}

	public function getGames() : array{
		return $this->games;
	}

	public function getInvites() : array{
		return $this->invites;
	}

	public function getInvitesTo(Player $player) : array{
		$invites = [];
		foreach($this->getInvites() as $invite){
			if($invite->getInvited() === $player) $invites[] = $invite;
		}
		return $invites;
	}

}