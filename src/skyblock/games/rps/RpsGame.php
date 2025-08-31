<?php namespace skyblock\games\rps;

use skyblock\SkyBlockPlayer as Player;

use core\utils\TextFormat;

class RpsGame{

	const TYPE_ROCK = 0;
	const TYPE_PAPER = 1;
	const TYPE_SCISSORS = 2;

	public int $round = 1;
	public array $roundWinners = [];

	public int $p1choice = -1;
	public int $p2choice = -1;

	public function __construct(
		public Player $player1,
		public Player $player2,
		public int $wager = 0,
		public int $rounds = 1
	){}

	public function getPlayer1() : Player{
		return $this->player1;
	}

	public function getPlayer2() : Player{
		return $this->player2;
	}

	public function getWager() : int{
		return $this->wager;
	}

	public function getRounds() : int{
		return $this->rounds;
	}

	public function getRound() : int{
		return $this->round;
	}

	public function isFinalRound() : bool{
		return $this->getRound() === $this->getRounds();
	}

	public function whoWins(int $choice1, int $choice2) : int{
		if($choice1 === $choice2) return -1;

		switch(true){
			case $choice1 === self::TYPE_ROCK && $choice2 === self::TYPE_PAPER:
				return $choice2;
			case $choice1 === self::TYPE_ROCK && $choice2 === self::TYPE_SCISSORS:
				return $choice1;
			case $choice1 === self::TYPE_PAPER && $choice2 === self::TYPE_ROCK:
				return $choice1;
			case $choice1 === self::TYPE_SCISSORS && $choice2 === self::TYPE_ROCK:
				return $choice2;

			case $choice1 === self::TYPE_PAPER && $choice2 === self::TYPE_SCISSORS:
				return $choice2;
			case $choice1 === self::TYPE_SCISSORS && $choice2 === self::TYPE_PAPER:
				return $choice1;
		}
	}

	public function setChoice(int $player, int $choice){
		switch($player){
			case 0;
				$this->p1choice = $choice;
				break;
			case 1:
				$this->p2choice = $choice;
				break;
		}
		if($this->p1choice !== -1 && $this->p2choice !== -1){
			$this->doRound($this->p1choice, $this->p2choice);
		}
	}

	public function doRound(int $player1choice, int $player2choice) : void{
		$winner = $this->whoWins($player1choice, $player2choice);
		if($winner === -1){
			//redo round
			return;
		}
		if($winner === $player1choice){
			$this->roundWinners[$this->getRound()] = 0;
			if($this->isFinalRound() || $this->majorityWon()){
				$this->end();
				return;
			}
		}elseif($winner === $player2choice){
			$this->roundWinners[$this->getRound()] = 1;
			if($this->isFinalRound() || $this->majorityWon()){
				$this->end();
				return;
			}
		}


	}

	public function majorityWon() : bool{
		$p1wins = 0;
		$p2wins = 0;
		foreach($this->roundWinners as $round => $winner){
			if($winner === 0){
				$p1wins++;
			}else{
				$p2wins++;
			}
		}
		return
			$p1wins >= $this->getRounds() / 2 ||
			$p2wins >= $this->getRounds() / 2;
	}

	public function end() : void{
		$p1wins = 0;
		$p2wins = 0;
		foreach($this->roundWinners as $round => $winner){
			if($winner === 0){
				$p1wins++;
			}else{
				$p2wins++;
			}
		}

		$player1 = $this->getPlayer1();
		$player2 = $this->getPlayer2();

		if($p1wins > $p2wins){
			$msg = TextFormat::GI . TextFormat::YELLOW . $player1->getName() . TextFormat::GRAY . " won the Rock Paper Scissors match!";
			$player1->addTechits($this->getWager());
		}elseif($p2wins > $p1wins){
			$msg = TextFormat::GI . TextFormat::YELLOW . $player1->getName() . TextFormat::GRAY . " won the Rock Paper Scissors match!";
			$player2->addTechits($this->getWager());
		}
		$player1->sendMessage($msg);
		$player2->sendMessage($msg);
		
		$this->terminate();
	}

	public function cancel() : void{

	}

	public function terminate() : void{
		//delete from player game sessions
		//delete from rps game store
	}

}