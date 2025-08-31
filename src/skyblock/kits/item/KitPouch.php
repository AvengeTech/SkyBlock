<?php

namespace skyblock\kits\item;

use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use skyblock\SkyBlockPlayer;

use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\item\MaxBook;
use skyblock\kits\Kits;
use skyblock\SkyBlock;

class KitPouch extends Item{

	public const TAG_INIT = "init";
	public const TAG_KIT = "kit";

	private string $kit = Kits::KIT_STARTER;

	public function setup(string $kit) : self{
		$this->kit = $kit;
		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);

		$this->setCustomName(TF::DARK_PURPLE . "Kit Pouch");
		$lores = [];
		$lores[] = TF::GRAY . "A pouch full of kit items.";
		$lores[] = TF::GRAY . " ";
		$lores[] = TF::GRAY . "Type: " . TF::BOLD . SkyBlock::getInstance()->getKits()->getKitByName($this->kit)->getDisplayName();
		$lores[] = TF::GRAY . " ";
		$lores[] = TF::GRAY . "This item is not claimable while";
		$lores[] = TF::GRAY . "in " . TF::RED . "PVP" . TF::GRAY . " or any combat areas.";
		$lores[] = TF::GRAY . " ";
		$lores[] = TF::GRAY . "Tap/Right-Click to use this item.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));
		return $this;
	}

	/** @param SkyBlockPlayer $player */
	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$gs = $player->getGameSession();

		if(
			$gs->getCombat()->getCombatMode()->inCombat() ||
			$gs->getCombat()->inPvPMode() ||
			SkyBlock::getInstance()->getCombat()->getArenas()->inArena($player) || 
			SkyBlock::getInstance()->getKoth()->inGame($player) || 
			SkyBlock::getInstance()->getLms()->inGame($player)
		){
			$player->sendMessage(TF::RI . "You can not claim kit while in pvp.");
			return ItemUseResult::FAIL();
		}

		$kit = SkyBlock::getInstance()->getKits()->getKitByName($this->kit);
		$kit->equip($player, false);

		$this->pop();

		$player->sendMessage(TF::GI . "You received the kit items!");

		return ItemUseResult::SUCCESS();
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$this->kit = $tag->getString(self::TAG_KIT, Kits::KIT_STARTER);
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setString(self::TAG_KIT, $this->kit);
	}
}