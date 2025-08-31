<?php namespace skyblock\combat;

use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
	RemoveActorPacket,
	types\LevelSoundEvent,
	types\entity\PropertySyncData
};
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;

use skyblock\SkyBlock;
use skyblock\combat\arenas\Arenas;
use skyblock\combat\command\PvP;
use skyblock\settings\SkyBlockSettings;

use core\utils\GenericSound;
use skyblock\SkyBlockPlayer;

class Combat{

	public Arenas $arenas;

	public function __construct(public SkyBlock $plugin){
		$this->arenas = new Arenas($plugin, $this);

		$plugin->getServer()->getCommandMap()->registerAll("combat", [
			new PvP($plugin, "pvp", "Enter PvP mode (WARNING: Players will be able to hit you)")
		]);
	}

	public function tick() : void{
		$this->getArenas()->doArenaTick();
	}

	public function removeLogs() : void{
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			/** @var SkyBlockPlayer $player */
			if($player->isLoaded()){
				$player->getGameSession()->getCombat()->getCombatMode()->reset(false);
			}
		}
	}

	public function getArenas() : Arenas{
		return $this->arenas;
	}

	public function strikeLightning(Position $pos, ?Entity $entity = null){
		$pk = new AddActorPacket();
		$pk->type = "minecraft:lightning_bolt";
		$pk->actorRuntimeId = $pk->actorUniqueId = $eid = Entity::nextRuntimeId();
		$pk->position = $pos->asVector3();
		$pk->yaw = $pk->pitch = 0;
		$pk->syncedProperties = new PropertySyncData([], []);
		if($entity instanceof Entity){
			$players = $entity->getViewers();
		}else{
			$players = $pos->getWorld()->getPlayers();
		}
		$p2d = [];
		foreach($players as $p){
			/** @var SkyBlockPlayer $p */
			if($p->isLoaded() && $p->getGameSession()->getSettings()->getSetting(SkyBlockSettings::LIGHTNING)){
				$p->getNetworkSession()->sendDataPacket($pk);
				$p2d[] = $p;
			}
		}
		$pos->getWorld()->addSound($pos, new GenericSound($pos, LevelSoundEvent::THUNDER));
		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($p2d, $eid) : void{
			$pk = new RemoveActorPacket();
			$pk->actorUniqueId = $eid;
			foreach($p2d as $p) if($p->isConnected()) $p->getNetworkSession()->sendDataPacket($pk);
		}), 20);
	}

}