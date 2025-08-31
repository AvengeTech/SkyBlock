<?php

namespace skyblock\item\inventory;

use core\block\tile\Chest;
use core\inventory\TempInventory;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Banner;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
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
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\Enchantments;
use skyblock\enchantments\item\CustomDeathTag;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\item\Nametag;
use skyblock\generators\item\Extender;
use skyblock\generators\item\Solidifier;
use skyblock\item\Essence;
use skyblock\item\inventory\specific\essence\EssenceSelectionInventory;
use skyblock\item\inventory\specific\pet\EggSelectionInventory;
use skyblock\item\Structure;
use skyblock\item\ui\specific\enchantments\BookRarityUI;
use skyblock\item\ui\specific\generator\EditExtenderUI;
use skyblock\item\ui\specific\generator\EditSolidifierUI;
use skyblock\item\ui\specific\pet\EditEnergyBoosterUI;
use skyblock\item\ui\specific\pet\EditGummyOrbUI;
use skyblock\pets\item\EnergyBooster;
use skyblock\pets\item\GummyOrb;
use skyblock\pets\item\PetEgg;
use skyblock\pets\item\PetKey;
use skyblock\shop\item\SellWand;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class SpecialItemsInventory extends SimpleInventory implements TempInventory{

	/** @var array<string, Position> */
	private array $locations = [];

	public CompoundTag $nbt;

	public function __construct(
		private int $increase = 0
	){
		parent::__construct(54);
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function setup() : self{
		$this->clearAll();
		$this->setItem($this->getIncreaseSlot(), $this->getIncreaseItem());

		foreach(Structure::INVENTORY_ITEMS as $key => $data){
			$item = StringToItemParser::getInstance()->parse($key);

			if(is_null($item)) continue;

			$item->setCustomName($data[Structure::DATA_NAME]);
			$item->setLore($data[Structure::DATA_DESCRIPTION]);
			$item->addEnchantment(EnchantmentRegistry::OOF()->getEnchantmentInstance());

			$count = $this->getIncreaseCounts()[$this->increase];
			$count = ($count > $item->getMaxStackSize() ? $item->getMaxStackSize() : $count);

			$item->setCount($count);

			$this->addItem($item);
		}

		return $this;
	}

	public function getIncreaseCounts() : array{
		return [
			0 => 1,
			1 => 16,
			2 => 32,
			3 => 64
		];
	}

	public function getIncreaseSlot() : int{
		return 49;
	}

	public function getIncreaseItem() : Item{
		return VanillaItems::BANNER()
		->setCustomName(TF::DARK_YELLOW . "Increase Count")
		->setLore([
			" ",
			TF::GRAY . "Click this item to increase all",
			TF::GRAY . "other item counts."
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

		if($item instanceof Banner){
			$this->increase++;

			if($this->increase > 3){
				$this->increase = 0;
			}

			$this->setup();
			return true;
		}elseif($item instanceof PetEgg){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			$player->setCurrentWindow(new EggSelectionInventory($item->getCount()));
			return true;
		}elseif($item instanceof EnchantmentBook){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player){
				$player->showModal(new BookRarityUI);
			}), 10);
			return true;
		}elseif($item instanceof Solidifier){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $item){
				$player->showModal(new EditSolidifierUI($item));
			}), 10);
			return true;
		}elseif($item instanceof Extender){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $item){
				$player->showModal(new EditExtenderUI($item));
			}), 10);
			return true;
		}elseif($item instanceof Essence){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();
			$player->setCurrentWindow(new EssenceSelectionInventory($item->getCount()));
		}elseif($item->equals(BlockRegistry::PET_BOX()->asItem(), false, false)){
			$item = BlockRegistry::PET_BOX()->addData($item);

			$player->getInventory()->addItem($item);
			return true;
		}elseif(
			$item instanceof PetKey || 
			$item instanceof CustomDeathTag ||
			$item instanceof SellWand ||
			$item instanceof Nametag
		){
			$item->init();

			$player->getInventory()->addItem($item);
			return true;
		}elseif($item instanceof EnergyBooster){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $item){
				$player->showModal(new EditEnergyBoosterUI($item));
			}), 10);
		}elseif($item instanceof GummyOrb){
			if(!is_null($player->getCurrentWindow())) $player->removeCurrentWindow();

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $item){
				$player->showModal(new EditGummyOrbUI($item));
			}), 10);
		}

		return false;
	}

	public function getNetworkType() : int{ return WindowTypes::CONTAINER; }

	public function getName() : string{ return "SpecialItemsInventory"; }

	public function getDefaultSize() : int{ return 54; }

	public function getTitle() : string{
		return TF::BOLD . TF::AQUA . "Special" . TF::GOLD . " Items";
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