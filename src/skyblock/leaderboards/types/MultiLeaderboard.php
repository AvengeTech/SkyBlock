<?php namespace skyblock\leaderboards\types;

class MultiLeaderboard extends Leaderboard implements MysqlUpdate{

	public int $index = 0;
	public int $ticks = 0;

	public function __construct(public array $leaderboards = [], public string $type = "", public int $autoUpdateTime = 3, public int $size = 10){
		parent::__construct($size);
	}
	
	public function getType() : string{
		return $this->type;
	}

	public function calculate() : void{
		foreach($this->getLeaderboards() as $leaderboard){
			$leaderboard->calculate();
		}
	}

	public function getTexts() : array{
		return $this->getCurrentLeaderboard()?->getTexts() ?? [];
	}
	
	public function tick() : void{
		$this->ticks++;
		if($this->ticks % $this->getAutoUpdateTime() === 0){
			$this->updateIndex();
		}
	}

	public function getIndex() : int{
		return $this->index;
	}

	public function updateIndex(bool $updateBoard = true) : void{
		$this->index++;
		if($this->index >= count($this->getLeaderboards())) $this->index = 0;
		if($updateBoard) $this->updateSpawnedTo();
	}
	
	public function getLeaderboards() : array{
		return $this->leaderboards;
	}

	public function getCurrentLeaderboard() : ?Leaderboard{
		return $this->leaderboards[$this->getIndex()] ?? null;
	}

	public function getAutoUpdateTime() : int{
		return $this->autoUpdateTime;
	}
	
}