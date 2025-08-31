<?php namespace skyblock\tags\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};
use skyblock\SkyBlockSession;

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class AddRandomTags extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["art"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addrandomtags <player> <amount>");
			return;
		}
		$name = array_shift($args);
		$amount = (int) array_shift($args);

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $amount) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $user, $amount) : void{
				$tags = SkyBlock::getInstance()->getTags();
				$new = [];
				$tdh = $session->getTags()->getTagsNoHave();
				if(count($tdh) <= $amount){
					$new = $tdh;
					$total = count($new);
				}else{
					$total = 0;
					while($total < $amount){
						$tag = $tags->getRandomTag($tdh);
						if(!in_array($tag, $new)){
							$new[] = $tag;
							$total++;
						}
					}
				}
				foreach($new as $t){
					$session->getTags()->addTag($t);
				}
				if($user->validPlayer()){
					$user->getPlayer()->sendMessage(TextFormat::GI . "You just received " . TextFormat::GREEN . $total . TextFormat::GRAY . " new tags!");
				}else{
					$session->getTags()->saveAsync();
				}
				$sender->sendMessage(TextFormat::GI . "Gave " . $user->getGamertag() . " " . $total . " random tags!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}