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

class AddTag extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addtag <player> <tagname>");
			return;
		}

		$name = array_shift($args);

		$tag = array_shift($args);
		$tags = SkyBlock::getInstance()->getTags();
		$tag = $tags->getTag($tag);

		if($tag === null){
			$sender->sendMessage(TextFormat::RI . "Tag doesn't exist!");
			return;
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $tag) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $user, $tag) : void{
				if($session->getTags()->hasTag($tag)){
					$sender->sendMessage(TextFormat::RI . $user->getGamertag() . " already has this tag!");
					return;
				}
				$session->getTags()->addTag($tag);
				if(!$user->validPlayer()){
					$session->getTags()->saveAsync();
				}
				$sender->sendMessage(TextFormat::GI . "Gave " . $user->getGamertag() . " the " . $tag->getName() . " tag");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}