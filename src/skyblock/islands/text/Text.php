<?php namespace skyblock\islands\text;

use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\world\Position;

use skyblock\SkyBlock;
use skyblock\islands\Island;
use skyblock\islands\text\entity\TextEntity;

use core\chat\emoji\EmojiLibrary;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class Text{

	public bool $changed = false;

	public Position $position;

	public ?TextEntity $entity = null;

	public function __construct(
		public TextManager $textManager,

		public int $created,

		public string $text,

		Vector3 $vector3,
	){
		$this->position = Position::fromObject($vector3, $textManager->getIsland()->getWorld());
	}

	public function isInitiated() : bool{
		return $this->entity !== null && !$this->entity->isClosed();
	}

	public function init() : void{
		$entity = new TextEntity(Location::fromObject($this->getPosition(), $this->getPosition()->getWorld()), null, $this);
		$entity->spawnToAll();
		$this->entity = $entity;
	}

	public function getTextEntity() : ?TextEntity{
		return $this->entity;
	}

	public function getTextManager() : TextManager{
		return $this->textManager;
	}

	public function getCreated() : int{
		return $this->created;
	}

	public function getText() : string{
		return $this->text;
	}

	public function setText(string $text) : void{
		$this->text = $text;
		$this->setChanged();
		if($this->isInitiated()){
			$this->getTextEntity()?->setNametag($this->getFormattedText());
		}
	}

	public function getFormattedText() : string{
		return str_replace("\\n", "\n", TextFormat::colorize(EmojiLibrary::convertWithEmojis($this->getText()), "&"));
	}

	public function getShortName(int $limit = 24) : string{
		$text = preg_replace("/[^A-Za-z0-9 \-]/", "", TextFormat::clean($this->getFormattedText()));
		return strlen($text) > $limit ? substr($text, 0, $limit) . "..." : $text;
	}

	public function getPosition() : Position{
		return $this->position;
	}

	public function updatePosition(float $x, float $y, float $z) : void{
		$this->position = Position::fromObject(new Vector3($x, $y, $z), $this->getTextManager()->getIsland()->getWorld());
		$this->setChanged();
		if($this->isInitiated()){
			$this->getTextEntity()?->teleport($this->getPosition());
		}
	}

	public function getChunkKey() : string{
		$x = (int) $this->getPosition()->getX() >> 4;
		$z = (int) $this->getPosition()->getZ() >> 4;
		return "$x:$z";
	}

	public function verify(Island $island) : bool{
		return isset($island->getTextManager()->texts[$this->getCreated()]);
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_island_text_" . $this->getTextManager()->getIsland()->getWorldName() . "_" . $this->getCreated(), new MySqlQuery("main",
				"DELETE FROM island_texts WHERE world=? AND created=?",
				[
					$this->getTextManager()->getIsland()->getWorldName(),
					$this->getCreated(),
				]
			)),
			function(MySqlRequest $request) : void{}
		);
	}

	public function save(bool $async = true) : void{
		if(!$this->hasChanged()) return;
		if($async){
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_text_" . $this->getTextManager()->getIsland()->getWorldName() . "_" . $this->getCreated(), new MySqlQuery("main",
					"INSERT INTO island_texts(world, created, textdata, posx, posy, posz) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						textdata=VALUES(textdata),
						posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz)",
					[
						$this->getTextManager()->getIsland()->getWorldName(),
						$this->getCreated(),
						$this->getText(),
						$this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ(),
					]
				)),
				function(MySqlRequest $request) : void{
					$this->setChanged(false);
				}
			);
		}else{
			$worldName = $this->getTextManager()->getIsland()->getWorldName();
			$created = $this->getCreated();
			$text = $this->getText();
			$x = $this->getPosition()->getX();
			$y = $this->getPosition()->getY();
			$z = $this->getPosition()->getZ();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare("INSERT INTO island_texts(world, created, textdata, posx, posy, posz) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						textdata=VALUES(textdata),
						posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz)"
			);
			$stmt->bind_param("sisddd", $worldName, $created, $text, $x, $y, $z);
			$stmt->execute();
			$stmt->close();

			$this->setChanged(false);
		}
	}

}
