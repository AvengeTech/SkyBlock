<?php namespace skyblock\islands\invite;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\user\User;

class Invite{

	const LIFETIME = 30;

	const STATUS_SENT = 0;
	const STATUS_ACCEPT = 1;
	const STATUS_DENY = 2;

	public int $created;

	public function __construct(
		public Island $island,
		public User $from,
		public User $to
	){
		$this->created = time();
	}

	public function tick() : bool{
		return $this->created + self::LIFETIME > time();
	}

	public function getIsland() : Island{
		return $this->island;
	}

	public function getFrom() : User{
		return $this->from;
	}

	public function getTo() : User{
		return $this->to;
	}

	public function accept() : void{
		$perm = $this->getIsland()->getPermissions()->addNewDefaultPermissions($this->getTo());
		/** @var SkyBlockPlayer $player */
		$player = $this->getTo()->getPlayer();
		if($player !== null){
			$player->getGameSession()->getIslands()?->addPermission($perm);
		}
		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "invite",
			"data" => [
				"island" => $this->getIsland()->getWorldName(),
				"to" => $this->getTo()->getGamertag(),
				"from" => $this->getFrom()->getGamertag(),
				"status" => self::STATUS_ACCEPT
			]
		]))->queue();
	}

	public function deny() : void{
		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "invite",
			"data" => [
				"island" => $this->getIsland()->getWorldName(),
				"to" => $this->getTo()->getGamertag(),
				"from" => $this->getFrom()->getGamertag(),
				"status" => self::STATUS_DENY
			]
		]))->queue();
	}

}