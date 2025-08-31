<?php

namespace skyblock\inventory;

use core\AtPlayer;
use pocketmine\{
	block\VanillaBlocks,
	inventory\Inventory,
	inventory\SimpleInventory,
	player\Player,
	Server,
	world\Position
};

use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	UpdateBlockPacket,

	types\CacheableNbt,
	types\BlockPosition
};
use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\{
	CompoundTag,
};
use pocketmine\block\tile\Nameable;
use pocketmine\network\mcpe\convert\TypeConverter;

use core\Core;
use core\inventory\TempInventory;
use core\staff\tasks\SeeinvDelayTask;

class EnderchestInventory extends SimpleInventory implements TempInventory {

	public int $updateTick = -1;
	public $nbt;

	public function __construct(public AtPlayer $player) {
		parent::__construct(27);
		$this->getListeners()->add(new EnderchestListener($this));

		$this->update();
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function update() {
		$this->updateTick = Server::getInstance()->getTick();
		$this->setContents($this->player->getEnderInventory()->getContents());
	}

	public function push() {
		$this->player->getSession()?->updateEnderInventory($this->getContents());
		$this->player->getSession()?->getEnderInv()?->pullFromPlayer();
	}

	public function getName(): string {
		return "EnderinvInventory";
	}

	public function getDefaultSize(): int {
		return 27;
	}

	public function getSize(): int {
		return 27;
	}

	public function getTitle(): string {
		return "Ender Chest";
	}

	public function getPlayer(): AtPlayer {
		return $this->player;
	}

	public function doOpen(): bool {
		$this->getPlayer()->getNetworkSession()->getInvManager()->getContainerOpenCallbacks()->add(function (int $id, Inventory $inventory): array {
			return []; //trollface
		});
		return $this->getPlayer()->setCurrentWindow($this);
	}

	public function onOpen(Player $who): void {
		parent::onOpen($who);
		$vec = $who->getPosition()->addVector($who->getDirectionVector()->multiply(-3.5))->round();
		$pos = new Position($vec->x, $vec->y, $vec->z, $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, $pos->x);
		$this->nbt->setInt(Tile::TAG_Y, $pos->y);
		$this->nbt->setInt(Tile::TAG_Z, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::ENDER_CHEST()->getStateId());
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new BlockActorDataPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->nbt = new CacheableNbt($this->nbt);
		$who->getNetworkSession()->sendDataPacket($pk);

		Core::getInstance()->getScheduler()->scheduleDelayedTask(new SeeinvDelayTask($who, $this, $pos), 4);
		$this->setContents($this->getPlayer()?->getEnderInventory()->getContents(true) ?? []);
	}

	public function onClose(Player $who): void {
		parent::onClose($who);
		$pos = new Position($this->nbt->getInt(Tile::TAG_X), $this->nbt->getInt(Tile::TAG_Y), $this->nbt->getInt(Tile::TAG_Z), $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, 0);
		$this->nbt->setInt(Tile::TAG_Y, 0);
		$this->nbt->setInt(Tile::TAG_Z, 0);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($pos)->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($pos->add(1, 0, 0)->floor())->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		//$who->removeCurrentWindow();
	}
}
