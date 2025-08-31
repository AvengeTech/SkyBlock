<?php namespace skyblock\islands\text;

use skyblock\islands\Island;

class TextManager{

	const TEXT_HARD_LIMIT = 50;
	const TEXT_MAX_SIZE = 128;

	const TEXTS_PER_LEVEL = 3;

	public array $chunkIndexes = [];

	public function __construct(
		public Island $island,
		public array $texts = []
	){}

	public function doInit() : void{
		foreach($this->getTexts() as $text){
			if(!$text->isInitiated()){
				$text->init();
			}
		}
	}

	public function save(bool $async = true) : void{
		foreach($this->getTexts() as $text){
			$text->save($async);
		}
	}

	/**
	 * Used if island is loaded before island world
	 */
	public function resetPositions() : void{
		foreach($this->getTexts() as $text){
			$text->updatePosition(
				$text->getPosition()->getX(),
				$text->getPosition()->getY(),
				$text->getPosition()->getZ(),
			);
		}
	}

	public function getIsland() : Island{
		return $this->island;
	}

	public function getTextLimit() : int{
		if(($island = $this->getIsland())->getSizeLevel() < 3) return 0;

		return min(self::TEXT_HARD_LIMIT, ($island->getSizeLevel() - 2) * self::TEXTS_PER_LEVEL);
	}

	public function getTexts() : array{
		return $this->texts;
	}

	public function getText(int $created) : ?Text{
		return $this->texts[$created] ?? null;
	}

	public function addText(Text $text) : void{
		$this->texts[$text->getCreated()] = $text;

		if(!isset($this->chunkIndexes[$key = $text->getChunkKey()])) $this->chunkIndexes[$key] = [];
		$this->chunkIndexes[$text->getChunkKey()][] = $text;
	}

	public function removeText(int $created, bool $delete = true) : void{
		$text = $this->texts[$created] ?? null;
		if($text !== null){
			if($text->isInitiated()){
				$text->getTextEntity()->despawnFromAll();
			}
			unset($this->texts[$created]);
			unset($this->chunkIndexes[$text->getChunkKey()]);
			if($delete){
				$text->delete();
			}
		}
	}

	public function delete() : void{
		foreach($this->getTexts() as $created => $text){
			$this->removeText($created);
		}
	}

}
