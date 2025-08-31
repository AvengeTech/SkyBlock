<?php namespace skyblock\parkour\course;

use pocketmine\world\Position;

class Course{

	public function __construct(
		public int $id,
		public string $name,
		public bool $active,

		public Position $beginning,
		public Position $start,
		public array $checkpoints, //in order
		public Position $end
	){}

	public function getId() : int{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	public function isActive() : bool{
		return $this->active;
	}
	
	public function getBeginningPosition() : Position{
		return $this->beginning;
	}

	public function getStartPosition() : Position{
		return $this->start;
	}

	public function getCheckpoints() : array{
		return $this->checkpoints;
	}

	public function getEndPosition() : Position{
		return $this->end;
	}

}