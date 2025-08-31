<?php namespace skyblock\islands;

class IslandBatch{

	public int $islandsLoaded = 0;
	public array $islands = [];

	public bool $complete = false;

	public function __construct(
		public int $id,
		public int $totalIslands,
		public \Closure $onCompletion,
	){}

	public function getId() : int{
		return $this->id;
	}

	public function getTotalIslands() : int{
		return $this->totalIslands;
	}

	public function getIslandsLoaded() : int{
		return $this->islandsLoaded;
	}

	public function addLoadedIsland(?Island $island) : bool{
		if($island !== null) $this->islands[$island->getWorldName()] = $island;
		$this->islandsLoaded++;

		if($this->getIslandsLoaded() >= $this->getTotalIslands() && !$this->isComplete()){
			$this->onCompletion()($this->getIslands());
			$this->setComplete();
			return true;
		}
		return false;
	}

	public function getIslands() : array{
		return $this->islands;
	}

	public function onCompletion() : \Closure{
		return $this->onCompletion;
	}

	public function isComplete() : bool{
		return $this->complete;
	}

	public function setComplete(bool $complete = true) : void{
		$this->complete = $complete;
	}

}