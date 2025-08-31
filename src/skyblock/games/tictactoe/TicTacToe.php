<?php namespace skyblock\games\tictactoe;

use pocketmine\player\Player;

use skyblock\SkyBlock;

class TicTacToe{
	
	public static int $gameId = 0;
	
	public array $games = [];
	
	public function __construct(SkyBlock $plugin){
		
	}
	
	public function tick() : void{
		
	}
	
	public function close() : void{
		foreach($this->getGames() as $game){
			
		}
	}
	
	public function getGames() : array{
		return $this->games;
	}
	
	public function createGame(Player $player1, Player $player2, int $wager = 0) : TicTacToeGame{
		
	}
	
}