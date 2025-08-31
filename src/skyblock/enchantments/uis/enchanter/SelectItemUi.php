<?php namespace skyblock\enchantments\uis\enchanter;

use pocketmine\player\Player;
use pocketmine\item\{
	Armor,
	Shovel,
	Hoe,
	Pickaxe,
	Axe,
	Shears,
	Sword,
	Durable,
    Item
};
use pocketmine\data\bedrock\EnchantmentIdMap;

use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\fishing\item\FishingRod;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown
};

use core\utils\TextFormat;
use skyblock\enchantments\ItemData;
use skyblock\enchantments\type\Enchantment;
use skyblock\SkyBlockPlayer;

class SelectItemUi extends CustomForm{

	const SWORD_LIMIT = 5;
	const ROD_LIMIT = 4;
	const ARMOR_LIMIT = 7;
	const TOOL_LIMIT = 8;

	const DAMAGE_ENCHANTS_LIMIT = 2;

	/** @var Item[] $items */
	public array $items = [];
	/** @var EnchantmentBook[] $enchantments */
	public array $enchantments = [];

	public function __construct(Player $player){
		/** @var SkyBlockPlayer $player */
		parent::__construct("Enchant Item");

		$this->addElement(new Label("Which item are you trying to enchant?"));

		$dropdown = new Dropdown("Item selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof Durable && $item->getCount() == 1){
				$this->items[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::WHITE . " (" . ($item->getMaxDurability() - $item->getDamage()) . " durability left)");
				$key++;
			}
		}
		if(empty($this->items)){
			$dropdown->addOption("You have nothing to enchant!");
		}
		$this->addElement($dropdown);

		$this->addElement(new Label("Select what enchantment you would like to apply to this item"));

		$dropdown = new Dropdown("Enchantment selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof EnchantmentBook && ($ench = $item->getEnchant()) !== null){
				$this->enchantments[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::AQUA . " (" . $ench->getTypeName() . " enchantment)");
				$key++;
			}
		}
		if(empty($this->enchantments)){
			$dropdown->addOption("You have no enchantment books!");
		}
		$this->addElement($dropdown);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(empty($this->items) || empty($this->enchantments)){
			return;
		}
		$item = $this->items[$response[1]];
		$book = $this->enchantments[$response[3]];

		if(!($item instanceof Hoe && $book->getEnchant()->getId() === ED::EFFICIENCY)){
			if(!ED::canEnchantWith($item, $book->getEnchant())){
				$player->sendMessage(TextFormat::RN . "You cannot apply a/n " . TextFormat::AQUA . $book->getEnchant()->getTypeName() . "enchantment" . TextFormat::GRAY . " to this item!");
				return;
			}
		}

		$ench = EnchantmentIdMap::getInstance()->fromId(($se = $book->getEnchant())->getId());
		if($item->hasEnchantment($ench)){
			$enchantment = $item->getEnchantment($ench);
			if($enchantment->getLevel() >= $se->getMaxLevel()){
				$player->sendMessage(TextFormat::RN . "This item already has this enchantment at it's highest level!");
				return;
			}
			if($book->getEnchant()->getStoredLevel() <= $enchantment->getLevel()){
				$player->sendMessage(TextFormat::RN . "This item already has this enchantment!");
				return;
			}
		}else{
			if(
				count($item->getEnchantments()) >= self::SWORD_LIMIT &&
				($item instanceof Sword)
			){
				$player->sendMessage(TextFormat::RN . "Swords can only have a maximum of " . self::SWORD_LIMIT . " enchantments applied!");
				return;
			}
			if(
				count($item->getEnchantments()) >= self::ROD_LIMIT &&
				$item instanceof FishingRod
			){
				$player->sendMessage(TextFormat::RN . "Fishing Rods can only have a maximum of " . self::ROD_LIMIT . " enchantments applied!");
				return;
			}
			if(
				count($item->getEnchantments()) >= self::TOOL_LIMIT &&
				($item instanceof Hoe || $item instanceof Shovel || $item instanceof Pickaxe || $item instanceof Axe || $item instanceof Shears)
			){
				$player->sendMessage(TextFormat::RN . "Tools can only have a maximum of " . self::TOOL_LIMIT . " enchantments applied!");
				return;
			}

			if(
				count($item->getEnchantments()) >= self::ARMOR_LIMIT &&
				$item instanceof Armor
			){
				$player->sendMessage(TextFormat::RN . "Armor can only have a maximum of " . self::ARMOR_LIMIT . " enchantments applied!");
				return;
			}

			if($item instanceof Sword && in_array($se->getId(), ED::DAMAGE_ENCHANTMENTS)){
				$data = new ItemData($item);

				if(
					$se->canOverclock() && 
					$se->getStoredLevel() >= $se->getMaxLevel() + 1
				){
					/** @var Enchantment $dataEnchantment */
					foreach($data->getEnchantments() as $dataEnchantment){
						if(
							$dataEnchantment->canOverClock() && 
							$dataEnchantment->getStoredLevel() >= $dataEnchantment->getMaxLevel() + 1
						){
							$player->sendMessage(TextFormat::RI . "Swords can only have a maximum of 1 overclocked enchantment applied!");
							return;
						}
					}
				}

				$damageEnchCount = 0;
				foreach($data->getEnchantments() as $enchantment){
					if(in_array($enchantment->getId(), ED::DAMAGE_ENCHANTMENTS)) $damageEnchCount++;
				}
				if($damageEnchCount >= 2){
					$player->sendMessage(TextFormat::RI . "Swords can only have a maximum of " . self::DAMAGE_ENCHANTS_LIMIT . " damage enchantments applied!");
					return;
				}
			}
		}

		$player->showModal(new EnchantConfirmUi($item, $book));
	}

}