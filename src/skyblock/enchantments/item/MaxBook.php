<?php namespace skyblock\enchantments\item;

use core\utils\ItemRegistry;
use pocketmine\item\{
	Item,
};
use pocketmine\nbt\{
	NBT,
	tag\ListTag,
	tag\CompoundTag
};
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\enchantments\EnchantmentData as ED;

use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentRegistry;

class MaxBook extends Item{

	const TAG_BOOK_RARITY = "rarity";
	const TAG_BOOK_TYPE = "type";
	const TAG_COST = "cost";
	const TAG_INCLUDE_DIVINE = 'includeDivine';
	const TAG_INIT = "init";
	const TAG_ENCH_CATEGORY = "enchCategory";

	const TYPE_MAX_RARITY = 1;
	const TYPE_MAX_RANDOM_RARITY = 2;

	private int $rarity = 1;
	private int $type = 1;
	private int $cost = -1;

	private int $enchantCategory = ED::CAT_UNIVERSAL;

	private bool $includeDivine = false;

	/**
	 * This must be the first function used to make the book functional.
	 */
	public function setup(int $type, int $rarity = -1, int $enchantCategory = ED::CAT_UNIVERSAL, bool $includeDivine = false): self {
		$this->rarity = $rarity;
		$this->type = $type;
		$this->enchantCategory = $enchantCategory;
		$this->includeDivine = $includeDivine;

		return $this->init();
	}

	/**
	 * Must use this function after the setup function
	 */
	public function init() : self {
		$nbt = $this->getNamedTag()->setByte(self::TAG_INIT, true);
		$this->setNamedTag($nbt);

		if($this->cost === -1) $this->setCost(($this->getType() === self::TYPE_MAX_RANDOM_RARITY ? ($this->includeDivine ? 25 : 20) : -1));
		
		$this->setCustomName($this->getBookName());

		$lores = [""];
		$lores[] = TF::GRAY . "Right click this book to receive";

		switch($this->getType()){
			case self::TYPE_MAX_RANDOM_RARITY:
				$lores[] = TF::GRAY . "a random max level enchantment";
				$lores[] = TF::GRAY . "from any rarity";
				break;
			
			case self::TYPE_MAX_RARITY:
				$lores[] = TF::GRAY . "a random max level enchantment";
				$lores[] = TF::GRAY . "from the " . $this->getRarityName() . TF::GRAY . " rarity";
				break;
		}

		$lores[] = " ";
		$lores[] = TF::YELLOW . "Cost: " . $this->cost . " XP Levels";

		if($this->includeDivine){
			$lores[] = " ";
			$lores[] = TF::GRAY . "(Includes " . TF::RED . TF::BOLD . "Divine" . TF::RESET . TF::GRAY . " Enchantments)";
		}

		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		return $this;
	}

	public function redeem(Player $player) : bool{
		$book = ItemRegistry::REDEEMED_BOOK();

		switch($this->getType()){
			case self::TYPE_MAX_RANDOM_RARITY:
				$chance = mt_rand(1, 100);

				if($this->includeDivine){
					if($chance <= 88){
						$rarity = mt_rand(1, 4);
					}else{
						$rarity = ED::RARITY_DIVINE;
					}
				}else{
					if($chance <= 35){
						$rarity = ED::RARITY_COMMON;
					}elseif($chance >= 36 && $chance <= 65){
						$rarity = ED::RARITY_UNCOMMON;
					}elseif($chance >= 66 && $chance <= 85){
						$rarity = ED::RARITY_RARE;
					}else{
						$rarity = ED::RARITY_LEGENDARY;
					}
				}

				$ench = EnchantmentRegistry::getRandomEnchantment($rarity);
				break;
			
			case self::TYPE_MAX_RARITY;
				$ench = EnchantmentRegistry::getRandomEnchantment($this->getRarity(), $this->getEnchantmentCategory());
				break;
		}
		
		$ench->setStoredLevel($ench->getMaxLevel());
		$book->setup($ench);
		
		if($this->getCount() > 1){
			if(!$player->getInventory()->canAddItem($book)){
				$player->sendMessage(TF::RN . "You can not redeem this book, your inventory is full.");
				return false;
			}

			$player->getInventory()->addItem($book);
			$this->pop();
			$player->getInventory()->setItemInHand($this);
		}else{
			$player->getInventory()->setItemInHand($book);
		}
		$player->sendMessage(TF::GI . "You received " . $book->getEnchant()->getLore($book->getEnchant()->getStoredLevel()) . TF::GRAY . " from your max book!");
		return true;
	}

	public function getRarityName(int $rarity = -1) : string{
		if($rarity === -1) $rarity = $this->getRarity();

		switch($rarity){
			case ED::RARITY_COMMON:
				return TF::RESET . TF::GREEN . "Common";
			case ED::RARITY_UNCOMMON:
				return TF::RESET . TF::DARK_GREEN . "Uncommon";
			case ED::RARITY_RARE:
				return TF::RESET . TF::YELLOW . "Rare";
			case ED::RARITY_LEGENDARY:
				return TF::RESET . TF::GOLD . "Legendary";
			case ED::RARITY_DIVINE:
				return TF::RESET . TF::RED . "Divine";
		}

		return ' ';
	}

	public function getBookName(int $rarity = -1, int $type = -1) : string{
		if($rarity === -1) $rarity = $this->getRarity();
		if($type === -1) $type = $this->getType();

		$name = $this->getRarityName($rarity) . ' ';
		$randomBook = TF::BOLD . TF::GREEN.'R'.TF::DARK_GREEN.'A'.TF::YELLOW.'N'.TF::GOLD.'D'.TF::RED .'O'.TF::RED.'M'.' '.TF::GOLD.'B'.TF::YELLOW.'O'.TF::DARK_GREEN.'O'.TF::GREEN . 'K';

		$types = [
			ED::CAT_PICKAXE => "Pickaxe",
			ED::CAT_TOOL => "Tool",
			ED::CAT_SWORD => "Sword",
			ED::CAT_ARMOR => "Armor",
			ED::CAT_FISHING_ROD => "Fishing Rod",
		];

		switch($type){
			case self::TYPE_MAX_RANDOM_RARITY:
				//$name = TF::BOLD . TF::DARK_PURPLE . TF::OBFUSCATED . 'ii' . TF::RESET . TF::BOLD . TF::AQUA.'R'.TF::DARK_GREEN.'A'.TF::GREEN.'N'.TF::YELLOW.'D'.TF::GOLD.'O'.TF::LIGHT_PURPLE.'M'.' '.TF::RED.'M'.TF::LIGHT_PURPLE.'A'.TF::GOLD.'X'.' '.TF::YELLOW.'B'.TF::GREEN.'O'.TF::DARK_GREEN.'O'.TF::AQUA.'K' . TF::DARK_PURPLE . TF::OBFUSCATED . 'ii' . TF::RESET;
				$name = $randomBook;
				break;
			case self::TYPE_MAX_RARITY:
				$name .= "Max Book";
				if ($this->getEnchantmentCategory() !== ED::CAT_UNIVERSAL) $name .= TF::RESET . TF::GRAY . " (" . $types[$this->getEnchantmentCategory()] . ")";
				break;
		}

		$name .= TF::RESET . TF::GRAY;

		return $name;
	}

	public function getMaxStackSize() : int{ return 64; }

	public function isInitiated() : bool{ return (bool) $this->getNamedTag()->getByte(self::TAG_INIT, false); }

	public function getRarity() : int{ return $this->rarity; }

	public function setRarity(int $rarity) : self{
		$this->rarity = $rarity;
		return $this;
	}

	public function getCost() : int{ return $this->cost; }

	public function setCost(int $cost = -1) : self{
		$rarity = $this->getRarity();
		$costs = [
			ED::RARITY_COMMON => 15,
			ED::RARITY_UNCOMMON => 30,
			ED::RARITY_RARE => 35,
			ED::RARITY_LEGENDARY => 45,
			ED::RARITY_DIVINE => 50
		];

		$this->cost = ($cost === -1 ? $costs[$rarity] : $cost);
		return $this;
	}

	public function getType() : int { return $this->type; }

	public function setType(int $type) : self{
		$this->type = $type;
		return $this;
	}

	public function getEnchantmentCategory(): int {
		return $this->enchantCategory;
	}

	public function setEnchantmentCategory(int $enchantCategory): self {
		$this->enchantCategory = $enchantCategory;
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$this->cost = $tag->getInt(self::TAG_COST, -1);
		$this->rarity = $tag->getInt(self::TAG_BOOK_RARITY, ED::RARITY_COMMON);
		$this->type = $tag->getInt(self::TAG_BOOK_TYPE, self::TYPE_MAX_RARITY);
		$this->enchantCategory = $tag->getInt(self::TAG_ENCH_CATEGORY, ED::CAT_UNIVERSAL);

		$this->includeDivine = $tag->getByte(self::TAG_INCLUDE_DIVINE, false);
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);

		if($tag->getByte("init", 0) == 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setInt(self::TAG_COST, $this->cost);
		$tag->setInt(self::TAG_BOOK_RARITY, $this->rarity);
		$tag->setInt(self::TAG_BOOK_TYPE, $this->type);
		$tag->setByte(self::TAG_INCLUDE_DIVINE, $this->includeDivine);
		$tag->setInt(self::TAG_ENCH_CATEGORY, $this->enchantCategory);
	}
}
