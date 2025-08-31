<?php namespace skyblock\trash\tiles;

use core\inventory\TempInventory;
use core\utils\Utils;
use pocketmine\block\tile\{Chest, Nameable, Tile};
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	UpdateBlockPacket,

	types\CacheableNbt,
	types\BlockPosition
};
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;
use skyblock\SkyBlock;
use skyblock\trash\tasks\TrashDelayTask;

class TrashInventory extends SimpleInventory implements TempInventory {

	public $nbt;
	public $trashid;
	/** @var array<string, Position> */
	public array $locations = [];

	public function __construct(int $id){
		parent::__construct(54);
		$this->trashid = $id;
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}

	public function getTrashId() : int{
		return $this->trashid;
	}

	public function getName() : string{
		return "TrashInventory";
	}

	public function getDefaultSize() : int{
		return 54;
	}

	public function getTitle() : string{
		return "Trash Can " . $this->getTrashId();
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

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new TrashDelayTask($who, $this, $pos), 4);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		if (!isset($this->locations[$who->getXuid()])) {
			Utils::dumpVals("Failed to remove trash chest for: " . $who->getName());
			return;
		}
		$pos = $this->locations[$who->getXuid()];
		unset($this->locations[$who->getXuid()]);

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