<?php namespace skyblock\games\tictactoe\inventory;

use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\{
	Chest,
	Nameable,
	Tile
};
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	ContainerOpenPacket,
	ContainerClosePacket,
	UpdateBlockPacket,
	types\CacheableNbt,
	types\BlockPosition
};
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\world\Position;

use prison\Prison;
use prison\vaults\Vault;
use prison\vaults\tasks\VaultDelayTask;

class TicTacToeInventory extends SimpleInventory{

	public CompoundTag $nbt;

	/**
	 * 0 - player1
	 * 1 - player2
	 */
	public int $turn = 0;

	const WINNING_COMBOS = [
		//horizontal
		[2, 4, 6],
		[20, 22, 24],
		[38, 40, 42],

		//vertical
		[2, 20, 38],
		[4, 22, 40],
		[6, 24, 42],

		//diagnol
		[2, 22, 42],
		[6, 22, 38],
	];

	public function __construct(public TicTacToeGame $game){
		parent::__construct(54);
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}

	public function getName() : string{
		return "TicTacToeInventory";
	}

	public function getDefaultSize() : int{
		return 54;
	}

	public function getTitle() : string{
		return "Tic Tac Toe";
	}

	public function getGame() : TicTacToeGame{
		return $this->game;
	}

	public function setup() : void{
		$gridSlots = [
			3, 5
			11, 12, 13, 14, 15,
			21, 23,
			29, 30, 31, 32, 33,
			39, 41
		];
	}

	public function getTurn() : int{
		return $this->turn;
	}

	public function setTurn(int $turn) : void{
		$this->turn = $turn;
	}

	public function nextTurn() : void{
		$this->setTurn($this->getTurn() == 1 ? 0 : 1);
	}

	public function getGameSlots() : array{
		return [
			2, 4, 6,
			20, 22, 24,
			38, 40, 42
		];
	}

	public function getPiece(int $player) : Item{
		switch($player){
			case 0;
				return VanillaItems::STICK();
				break;
			case 1;
				return VanillaItems::COAL();
				break;
		}
	}

	/**
	 * returns false or winning combo key (so it can be highlighted)
	 */
	public function hasWin(int $player) : bool|int{
		$item = $this->getPiece($player);
		$win = false;
		foreach(self::WINNING_COMBOS as $key => $combo){
			$row = 0;
			foreach($combo as $slot){
				if($this->getItem($slot) === $item){
					$row++;
				}
			}
			if($row === 3){
				$win = $key;
			}
		}
		return $win;
	}

	public function getWinningItems(int $player) : array{
		$win = $this->hasWin($player);
		if($win === false) return [];
		$slots = self::WINNING_COMBOS[$win];
		$items = [];
		foreach($slots as $slot){
			$items[] = $this->getItem($slot);
		}
		return $items;
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
		$pos = new Position($who->getPosition()->getFloorX(), $who->getPosition()->getFloorY() + 2, $who->getPosition()->getFloorZ(), $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, $pos->x);
		$this->nbt->setInt(Tile::TAG_Y, $pos->y);
		$this->nbt->setInt(Tile::TAG_Z, $pos->z);

		$this->nbt->setInt(Chest::TAG_PAIRX, $pos->x + 1);
		$this->nbt->setInt(Chest::TAG_PAIRZ, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::CHEST()->getFullId());
		$who->getNetworkSession()->sendDataPacket($pk);
		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::CHEST()->getFullId());
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new BlockActorDataPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->nbt = new CacheableNbt($this->nbt);
		$who->getNetworkSession()->sendDataPacket($pk);

		Prison::getInstance()->getScheduler()->scheduleDelayedTask(new VaultDelayTask($who, $this, $pos), 4);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$pos = new Position($this->nbt->getInt(Tile::TAG_X), $this->nbt->getInt(Tile::TAG_Y), $this->nbt->getInt(Tile::TAG_Z), $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, 0);
		$this->nbt->setInt(Tile::TAG_Y, 0);
		$this->nbt->setInt(Tile::TAG_Z, 0);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId($who->getWorld()->getBlock($pos)->getFullId());
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId($who->getWorld()->getBlock($pos->add(1, 0, 0)->floor())->getFullId());
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		($c = $this->getVault()->getComponent())->setChanged();
		if(!$c->getPlayer() instanceof Player){
			$c->saveAsync();
		}
	}

}