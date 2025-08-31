<?php

namespace skyblock\item\inventory\specific\enchantments;

use core\block\tile\Chest;
use core\inventory\TempInventory;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\Enchantments;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\type\Enchantment;
use skyblock\item\ui\specific\enchantments\BookRarityUI;
use skyblock\item\ui\specific\enchantments\EditBookUI;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class BookSelectionInventory extends SimpleInventory implements TempInventory{

	/** @var array<string, Position> */
	public array $locations = [];
	private int $increaseLevel = 1;

	private CompoundTag $nbt;

	public function __construct(
		private int $rarity
	){
		parent::__construct(54);
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function setup() : self{
		$this->clearAll();

		$this->setItem($this->getBackSlot(), $this->getBackItem());
		$this->setItem($this->getIncreaseSlot(), $this->getIncreaseItem());

		$higestLevel = 1;

		/** @var Enchantment $enchantment */
		foreach(EnchantmentRegistry::getEnchantments($this->rarity) as $enchantment){
			$level = $this->increaseLevel;
			$level = ($level > $enchantment->getMaxLevel() && !$enchantment->canOverclock() ? $enchantment->getMaxLevel() : ($level - $enchantment->getMaxLevel() >= 2 ? $enchantment->getMaxLevel() + 1 : $level));

			$enchantment->setStoredLevel($level);

			$item = ItemRegistry::REDEEMED_BOOK();
			$item->setup($enchantment);

			$lores = [];
			$lores[] = TF::AQUA . $enchantment->getTypeName() . "enchantment";

			if($level >= $higestLevel) $higestLevel = $enchantment->getMaxLevel();

			if($level > $enchantment->getMaxLevel()){
				$lores[] = " ";
				$lores[] = TF::GRAY . "Book is overclocked";
			}

			$item->setLore($lores);

			if($this->canAddItem($item)) $this->addItem($item);
		}

		if($higestLevel <= $this->increaseLevel){
			$this->increaseLevel = 0;
		}

		return $this;
	}

	public function getBackSlot() : int{
		return 45;
	}

	public function getBackItem() : Item{
		return VanillaItems::BANNER()
		->setColor(DyeColor::RED())
		->setCustomName(TF::DARK_YELLOW . "Back")
		->setLore([
			" ",
			TF::GRAY . "Click this item to go back",
			TF::GRAY . "to the previous menu."
		])->addEnchantment(EnchantmentRegistry::OOF()->getEnchantmentInstance());
	}

	public function getIncreaseSlot() : int{
		return 53;
	}

	public function getIncreaseItem() : Item{
		return VanillaItems::BANNER()
		->setCustomName(TF::DARK_YELLOW . "Increase Level")
		->setLore([
			" ",
			TF::GRAY . "Click this item the level of the",
			TF::GRAY . "enchantment books."
		])->addEnchantment(EnchantmentRegistry::OOF()->getEnchantmentInstance());
	}

	/** @param SkyBlockPlayer $player */
	public function handle(Player $player, Item $item) : bool{
		if(!$item->isNull()){
			$sound = PlaySoundPacket::create(
				"note.pling",
				$player->getPosition()->x,
				$player->getPosition()->y,
				$player->getPosition()->z,
				0.5,
				1.0
			);

			NetworkBroadcastUtils::broadcastPackets([$player], [$sound]);
		}

		if($item->equalsExact($this->getBackItem())){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player){
				$player->showModal(new BookRarityUI);
			}), 10);
			return true;
		}elseif($item->equalsExact($this->getIncreaseItem())){
			$this->increaseLevel++;

			if($this->increaseLevel > 6){
				$this->increaseLevel = 1;
			}

			$this->setup();
			return true;
		}elseif($item instanceof EnchantmentBook){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $item){
				$player->showModal(new EditBookUI($item));
			}), 10);
			return true;
		}

		return false;
	}

	public function getNetworkType() : int{ return WindowTypes::CONTAINER; }

	public function getName() : string{ return "EnchantmentsInventory"; }

	public function getDefaultSize() : int{ return 54; }

	public function getTitle() : string{
		return ED::rarityColor($this->rarity) . ED::rarityName($this->rarity) . " Selection";
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
		$vec = $who->getPosition()->addVector($who->getDirectionVector()->multiply(-3.5))->round();
		$pos = new Position($vec->x, $vec->y, $vec->z, $who->getWorld());

		$this->locations[$who->getXuid()] = $pos;

		$nbt = clone $this->nbt;
		$nbt->setInt(Tile::TAG_X, $pos->x);
		$nbt->setInt(Tile::TAG_Y, $pos->y);
		$nbt->setInt(Tile::TAG_Z, $pos->z);

		$nbt->setInt(Chest::TAG_PAIRX, $pos->x + 1);
		$nbt->setInt(Chest::TAG_PAIRZ, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
		$who->getNetworkSession()->sendDataPacket($pk);
		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new BlockActorDataPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->nbt = new CacheableNbt($nbt);
		$who->getNetworkSession()->sendDataPacket($pk);

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($who, $pos){
			if($who->isConnected()) {
				$id = $who->getNetworkSession()->getInvManager()->getWindowId($this);
				if ($id === null) return;
				$pk = new ContainerOpenPacket();
				$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
				$pk->windowId = $id;
				$pk->windowType = WindowTypes::CONTAINER;

				$who->getNetworkSession()->sendDataPacket($pk);
				$who->getNetworkSession()->getInvManager()->syncContents($this);

				$this->setup();
			}
		}), 10);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);

		$pos = $this->locations[$who->getXuid()];
		unset($this->locations[$who->getXuid()]);

		$this->nbt->setInt(Tile::TAG_X, 0);
		$this->nbt->setInt(Tile::TAG_Y, 0);
		$this->nbt->setInt(Tile::TAG_Z, 0);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($pos->getWorld()->getBlock($pos)->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($pos->getWorld()->getBlockAt($pos->x + 1, $pos->y, $pos->z)->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);
	}
}