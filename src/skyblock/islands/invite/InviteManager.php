<?php namespace skyblock\islands\invite;

use pocketmine\player\Player;

use skyblock\islands\Island;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\user\User;
use core\utils\TextFormat;

class InviteManager{

	public array $invites = [];

	public function tick() : void{
		foreach($this->getInvites() as $player => $invites){
			foreach($invites as $island => $invite){
				if(!$invite->tick()){
					$invite->deny();
					if(($pl = $invite->getFrom()->getPlayer()) !== null){
						$pl->sendMessage(TextFormat::RI . $invite->getTo()->getGamertag() . " denied your island invite");
					}
					$this->removeInvite($player, $island);
				}
			}
		}
	}

	public function getInvites() : array{
		return $this->invites;
	}

	public function getInvitesFor(Player|User $player) : array{
		return $this->getInvites()[$player->getName()] ?? [];
	}
	
	public function getInvitesTo(Island $island) : int{
		$count = 0;
		foreach($this->getInvites() as $player => $invites){
			foreach($invites as $is => $invite){
				if($is == $island->getWorldName()) $count++;
			}
		}
		return $count;
	}
	
	public function sendInvite(Invite $invite) : void{
		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "invite",
			"data" => [
				"island" => $invite->getIsland()->getWorldName(),
				"to" => $invite->getTo()->getGamertag(),
				"from" => $invite->getFrom()->getGamertag(),
				"status" => Invite::STATUS_SENT
			]
		]))->queue();
		
		$this->addInvite($invite);
	}

	public function addInvite(Invite $invite) : void{
		if(!isset($this->invites[$invite->getTo()->getGamertag()])){
			$this->invites[$invite->getTo()->getGamertag()] = [];
		}
		$this->invites[$invite->getTo()->getGamertag()][$invite->getIsland()->getWorldName()] = $invite;
		if(($pl = $invite->getTo()->getPlayer()) instanceof Player){
			$pl->sendMessage(TextFormat::GI . "You received an island invite from " . TextFormat::YELLOW . $invite->getFrom()->getGamertag() . TextFormat::GRAY . ". Type " . TextFormat::AQUA . "/is invites" . TextFormat::GRAY . " to view it!");
		}
	}

	public function hasInviteTo(string|Player|User $user, string|Island $island) : bool{
		if(!isset($this->invites[is_string($user) ? $user : $user->getName()])){
			return false;
		}

		return isset($this->invites[is_string($user) ? $user : $user->getName()][is_string($island) ? $island : $island->getWorldName()]);
	}

	public function removeInvite(string $to, string $island) : void{
		unset($this->invites[$to][$island]);
	}

}