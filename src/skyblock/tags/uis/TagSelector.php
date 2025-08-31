<?php namespace skyblock\tags\uis;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\tags\Structure;

use core\network\Links;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class TagSelector extends SimpleForm{

	public $session;

	public array $tags = [];

	public function __construct(Player $player){
		/** @var SkyBlockPlayer $player */
		$session = $this->session = $player->getGameSession()->getTags();
		$tags = $session->getTags();
		$t = [];
		$key = 0;
		foreach($tags as $tag){
			$t[$key] = $tag;
			$key++;
		}
		$this->tags = $t;

		$tc = count($this->tags);
		$total = count(Structure::TAG_FORMAT);

		parent::__construct("Tag Selector", "You have " . $tc . "/" . $total . " tags unlocked\n\nUnlock more by opening Crates, or purchase them at " . TextFormat::YELLOW . Links::SHOP);

		$this->addButton(new Button(TextFormat::RED . "Remove Tag"));
		foreach($this->tags as $name => $tag){
			$this->addButton(new Button($tag->getFormat() . TextFormat::RESET . "\n" . TextFormat::DARK_PURPLE . "Tap to select!"));
		}
	}

	public function handle($response, Player $player){
		$tags = SkyBlock::getInstance()->getTags();
		$session = $this->session;
		if($response == 0){
			$session->setActiveTag();
			$player->sendMessage(TextFormat::GREEN . "Tag disabled.");
			return;
		}
		foreach($this->tags as $key => $tag){
			if($key == $response - 1){
				if(!$session->hasTag($tag)){
					$player->sendMessage(TextFormat::RED."You do not have this tag unlocked!");
					return;
				}
				$session->setActiveTag($tag);
				$player->sendMessage(TextFormat::GREEN."You now have the " . $tag->getName() . " tag equipped!");
				return;
			}
		}
	}

}