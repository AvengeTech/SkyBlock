<?php namespace skyblock\islands\world\generator;

use pocketmine\world\generator\GeneratorManager;

class GeneratorInfo{
	
	public function __construct(
		public int $id,
		public string $name,
		public string $class,
		public string $icon
	){}
	
	public function getId() : int{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}
	
	public function getClass() : string{
		return $this->class;
	}
	
	public function getGenerator() : ?IslandGenerator{
		return GeneratorManager::getInstance()->getGenerator("island_" . $this->getName());
	}
	
}