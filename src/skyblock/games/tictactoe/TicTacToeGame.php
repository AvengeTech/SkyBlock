<?php namespace skyblock\games\tictactoe;

use pocketmine\player\Player;

class TicTacToeGame{
	
	public TicTacToeInventory $inventory;
	
	public function __construct(
		public Player $player1,
		public Player $player2,
		public int $wager = 0
	){
		
	}
	
	public function getPlayer1() : Player{
		return $this->player1;
	}
	
	public function getPlayer2() : Player{
		return $this->player2;
	}
	
	public function getWager() : int{
		return $this->wager;
	}
	
}