<?php namespace skyblock;

use Generator;

use pocketmine\scheduler\Task;

use skyblock\spawners\tile\Spawner;

use core\Core;
use core\utils\AsyncIterator;
use pocketmine\block\tile\Tile;
use pocketmine\Server;
use skyblock\hoppers\tile\HopperTile;

class MainTask extends Task{

	public int $ktimer = 0;
	public int $runs = 0;

	public function __construct(public SkyBlock $plugin){
		$this->resetKothTimer();
	}

	public function resetKothTimer() : void{
		if(Core::thisServer()->isSubServer()){
			$this->ktimer = PHP_INT_MAX;
			return;
		}
		$this->ktimer = time() + (60 * 30);
	}

	public function doSpawnerTicks() {
		$lastSetTick = 0;
		foreach ($this->plugin->spawnerStore as $id => $spawner) {
			if ($spawner->nextTick < 0) {
				$spawner->nextTick = Server::getInstance()->getTick() + ($lastSetTick += 3);
			} else $spawner->tick(Server::getInstance()->getTick());
		}
	}

	public function doHopperTicks() {
		$lastSetTick = 0;
		foreach ($this->plugin->hopperStore as $id => $hopper) {
			if ($hopper->nextTick < 0) {
				$hopper->nextTick = Server::getInstance()->getTick() + ($lastSetTick += 3);
			} elseif (Server::getInstance()->getTick() >= $hopper->nextTick) {
				/** @var Tile $hopper */
				$pos = $hopper->getPosition();
				/** @var HopperTile $hopper */
				if ($pos->isValid() && $pos->getWorld()->isChunkLoaded($pos->x >> 4, $pos->z >> 4)) {
					$hopper->getBlock()->onScheduledUpdate();
					$hopper->tick();
				}
			}
		}
	}

	public function onRun() : void{
		$this->runs++;
		if($this->runs % 5 == 0){
			$this->plugin->getSessionManager()?->tick();
			$this->plugin->getCombat()->tick();
		}

		$this->doSpawnerTicks();
		#$this->doHopperTicks();
		

		$iterator = Core::getIterator();

		if ($this->runs % 20 == 0) { //1 second
			$hoppers = $this->plugin->hopperStore;
			$iterator::iterate(function() use($hoppers) : Generator{
				foreach($hoppers as $id => $hopper){
					try {
						/** @var Tile $hopper */
						$pos = $hopper->getPosition();
						/** @var HopperTile $hopper */
						$pos->getWorld()->getBlock($pos)->onScheduledUpdate();
						yield true;
					}catch(\Error $e){
						yield false;
					}
				}
				yield false;
			}, 3);
					    
			if(!Core::thisServer()->isSubServer()){
				$this->plugin->getAuctionHouse()->tick();
			}
			$this->plugin->getGames()->tick();
			$this->plugin->getHud()->tick();
			$this->plugin->getKoth()->tick();
			$this->plugin->getLms()->tick();
			$this->plugin->getLeaderboards()->tick();
			$this->plugin->getTrash()->tick();

			$this->plugin->getIslands()->tick();

			$ts = Core::thisServer();
			if(!$ts->isSubServer() && time() > $this->ktimer){
				$koth = $this->plugin->getKoth();
				if(count($koth->getActiveGames()) === 0){
					$koth->startKoth();
					$this->resetKothTimer();
				}
			}
		}
	}

}
