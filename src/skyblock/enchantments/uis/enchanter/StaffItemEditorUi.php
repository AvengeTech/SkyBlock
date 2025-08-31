<?php namespace skyblock\enchantments\uis\enchanter;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\player\Player;
use pocketmine\item\{
	Durable
};

use skyblock\SkyBlock;
use skyblock\enchantments\ItemData;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown,
	Input
};

use core\utils\TextFormat;
use skyblock\enchantments\EnchantmentRegistry;

class StaffItemEditorUi extends CustomForm{

	public $items = [];

	public function __construct(Player $player){
		parent::__construct("Staff Item Editor");

		$this->addElement(new Label("Please enter the item information below!"));

		$dropdown = new Dropdown("Select Item");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof Durable){
				$this->items[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::WHITE . " (" . $item->getDamage() . " uses)");
				$key++;
			}
		}
		if(empty($this->items)){
			$dropdown->addOption("You have nothing to change!");
		}
		$this->addElement($dropdown);

		$this->addElement(new Input("New name", "blank for no change"));
		$this->addElement(new Input("New death tag", "blank for no change"));
		$this->addElement(new Input("Add enchantments", "pierce 4, magnify 5, etc"));
		$this->addElement(new Input("Take enchantments", "pierce 4, magnify 5, etc"));
		$this->addElement(new Input("Set blocks mined", "blank for no change"));
		$this->addElement(new Input("Set kills", "blank for no change"));
		$this->addElement(new Input("Set level", "blank for no change"));
		$this->addElement(new Input("Set xp", "blank for no change"));
		$this->addElement(new Input("Set skill points", "blank for no change"));
		$this->addElement(new Input("Set prestige", "blank for no change"));
		$this->addElement(new Input("Signed by (; to remove)", "blank for no change"));

		$this->addElement(new Label("Wow! That's a lot of item properties..."));
	}

	public function handle($response, Player $player){
		if(empty($this->items)){
			return;
		}
		$item = $this->items[$response[1]];
		if(($slot = $player->getInventory()->first($item, true)) == -1){
			$player->sendMessage(TextFormat::RED . "This item is no longer in your inventory! Hopefully you didn't spend a long time editing this item!");
			return;
		}

		$data = new ItemData($player->getInventory()->getItem($slot));

		$name = $response[2];
		if($name != "") $data->setCustomName($name);

		$death = $response[3];
		if($death != "") $data->setDeathMessage($death);

		$add = $response[4];
		$aa = [];
		if($add !== ""){
			$ae = explode(", ", $add);
			foreach($ae as $ee){
				$eee = explode(" ", $ee);
				if(empty($eee)) continue;

				$name = "";
				$level = -1;
				while(count($eee) > 0){
					$next = array_shift($eee);
					if(is_numeric($next)){
						$level = (int) $next;
					}else{
						$name .= $next . " ";
					}
				}
				$name = rtrim($name);

				if($level == 0) continue;

				$ench = EnchantmentRegistry::getEnchantmentByName($name, true);
				if($ench === null) continue;

				$ench->setStoredLevel(($level == -1 ? $ench->getMaxLevel() : $level), true);
				$aa[] = $ench;
			}
		}
		foreach($aa as $a){
			$data->addEnchantment($a, $a->getStoredLevel());
		}

		$remove = $response[5];
		$ra = [];
		if($remove !== ""){
			$re = explode(", ", $remove);
			foreach($re as $ee){
				$eee = explode(" ", $ee);
				if(empty($eee)) continue;

				$name = strtolower(array_shift($eee));
				$level = (int) (array_shift($eee) ?? -1);

				$ench = EnchantmentRegistry::getEnchantmentByName($name, true);
				if($ench === null) continue;

				$ench->setStoredLevel($level, true);
				$ra[] = $ench;
			}
		}
		foreach($ra as $a){
			if (!is_null(EnchantmentIdMap::getInstance()->fromId($a->getRuntimeId())) && $data->getItem()->hasEnchantment(EnchantmentIdMap::getInstance()->fromId($a->getRuntimeId()))) {
				$data->removeEnchantment($a->getRuntimeId(), $a->getStoredLevel());
			}
		}

		$blocks = (int) $response[6];
		if($blocks > 0)
			$data->setBlocksMined($blocks);
		
		$kills = (int) $response[7];
		if($kills > 0)
			$data->setKills($kills);

		$level = (int) $response[8];
		if($level > 0)
			$data->setLevel($level);

		$xp = (int) $response[9];
		if($xp > 0)
			$data->setXp($xp);

		$points = (int) $response[10];
		if($points > 0){
			$data->setSkillPoints($points);
		}

		$pres = (int) $response[11];
		if($pres > 0){
			$data->setPrestige($pres);
		}

		$signed = $response[12];
		if($signed != ""){
			if($signed == ";"){
				$data->unsign();
			}else{
				$data->sign($signed);
			}
		}

		$data->getItem()->setLore($data->calculateLores()); // just to make sure

		$player->getInventory()->setItem($slot, $data->getItem());
		$player->sendMessage(TextFormat::GI . "Successfully edited item properties!");
	}

}
