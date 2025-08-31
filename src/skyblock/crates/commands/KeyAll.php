<?php namespace skyblock\crates\commands;

use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\rank\Rank;
use core\utils\TextFormat;

class KeyAll extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		if(count($args) < 1){
			$sender->sendMessage(TextFormat::RN . "Usage: /keyall <type> [amount]");
			return;
		}

		$type = strtolower(array_shift($args));
		$amount = 1;
		if(isset($args[0])) $amount = (int) array_shift($args);

		if($amount <= 0 || $amount > 10){
			$sender->sendMessage(TextFormat::RN . "Amount must be a number between 1 and 10");
			return;
		}

		if(!in_array($type, ["iron", "gold", "diamond", "emerald", "vote", "divine"])){
			$sender->sendMessage(TextFormat::RN . "Invalid key type!");
			return;
		}

		$colors = [
			"iron" => TextFormat::WHITE,
			"gold" => TextFormat::GOLD,
			"diamond" => TextFormat::AQUA,
			"emerald" => TextFormat::GREEN,
			"vote" => TextFormat::YELLOW,
			"divine" => TextFormat::RED,
		];
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
			/** @var SkyBlockPlayer $player */
			if($player->isLoaded()){
				$player->sendMessage(TextFormat::GRAY . "Everyone online has received " . TextFormat::GREEN . "+" . $amount . " " . $colors[$type] . TextFormat::BOLD . strtoupper($type) . TextFormat::RESET . TextFormat::GREEN . " keys!");
				$session = $player->getGameSession()->getCrates();
				$session->addKeys($type, $amount);
			}
		}

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "keyall",
			"data" => [
				"type" => $type,
				"amount" => $amount,
			]
		]))->queue();
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}