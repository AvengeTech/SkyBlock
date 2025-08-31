<?php namespace skyblock\trash\tasks;

use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\{
	BlockPosition,
	inventory\WindowTypes
};

use pocketmine\scheduler\Task;
use pocketmine\player\Player;

use pocketmine\world\Position;
use skyblock\trash\tiles\TrashInventory;

class TrashDelayTask extends Task{

	private $player;
	private $inventory;
	private $pos;

	public function __construct(Player $player, TrashInventory $inventory, Position $pos){
		$this->player = $player;
		$this->inventory = $inventory;
		$this->pos = $pos;
	}

	public function onRun() : void{
		$pos = $this->pos;
		if($this->player->isConnected()) {
			$id = $this->player->getNetworkSession()->getInvManager()->getWindowId($this->inventory);
			if ($id === null) return;
			$pk = new ContainerOpenPacket();
			$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
			$pk->windowId = $id;
			$pk->windowType = WindowTypes::CONTAINER;

			$this->player->getNetworkSession()->sendDataPacket($pk);
			$this->player->getNetworkSession()->getInvManager()->syncContents($this->inventory);
		}
	}

}