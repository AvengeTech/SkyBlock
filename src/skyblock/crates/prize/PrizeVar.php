<?php namespace skyblock\crates\prize;

class PrizeVar{

	public $key = "";
	public $count = 0;

	public $extra = [];

	public function __construct(array $data){
		$this->parse($data);
	}

	public function parse(array $data) : void{
		if(count($data) == 1) return;

		$key = array_shift($data);
		$count = (int) array_shift($data);

		$this->key = $key;
		$this->count = $count;

		$this->extra = $data;
	}

	public function getKey() : string{
		return $this->key;
	}

	public function getCount() : int{
		return $this->count;
	}

	public function getExtra() : array{
		return $this->extra;
	}

}