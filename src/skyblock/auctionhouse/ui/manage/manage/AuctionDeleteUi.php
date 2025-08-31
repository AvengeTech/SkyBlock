<?php

namespace skyblock\auctionhouse\ui\manage\manage;

use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\auctionhouse\ui\manage\AuctionManageUi;
use skyblock\auctionhouse\Auction;

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class AuctionDeleteUi extends ModalWindow {

	public function __construct(public Auction $auction) {
		parent::__construct(
			"Remove" . $auction->getName(),
			"Are you sure you would like to remove this item from the auction house?",
			"Remove auction",
			"Go back"
		);
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$am = SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager();
		$auction = $am->getAuctionByAuction($this->auction);
		if ($auction === null) {
			$player->showModal(new AuctionManageUi($player, "This auction is no longer on the auction house!"));
			return;
		}
		if ($response) {
			$inv = $auction->return();
			$am->removeAuction($auction);
			$player->showModal(new AuctionManageUi($player), "Auction has successfully been removed from the auction house and returned to your " . ($inv ? "inventory" : "inbox") . "!");
			$type = Core::getInstance()->getNetwork()->getServerType();
			$post = new Post("", "AuctionHouse - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $player->getName() . "** took **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "** off the auction house!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field('Sent To', ($inv ? "Inventory" : "Inbox"), true)
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("auctions-" . $type));
			$post->send();
		} else {
			$player->showModal(new AuctionViewUi($auction));
		}
	}
}
