<?php

namespace skyblock\auctionhouse\ui\select\auction;

use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\player\Player;

use core\ui\windows\ModalWindow;

use skyblock\auctionhouse\Auction;
use skyblock\auctionhouse\ui\select\AuctionSelectUi;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\utils\TextFormat;

class BuyAuctionUi extends ModalWindow {

	public function __construct(public Auction $auction, public int $prevPage = 1, public array $auctions = []) {
		parent::__construct("Buy now", "Are you sure you want to buy this auction now for " . TextFormat::AQUA . number_format($auction->getBuyNowPrice()) . " techits?", "Buy now", "Cancel");
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$am = SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager();
		$auction = $am->getAuctionByAuction($this->auction);
		if ($auction === null) {
			$player->showModal(new AuctionSelectUi($this->auctions, $this->prevPage, "This auction has expired! You were too late ;-;"));
			return;
		}
		if ($response) {
			if (!$auction->canBuyNow($player)) {
				$player->showModal(new SingleAuctionUi($auction, $this->prevPage, $this->auctions, "You do not have enough techits to purchase this auction!"));
				return;
			}
			if (!$player->getInventory()->canAddItem($auction->getItem())) {
				$player->showModal(new SingleAuctionUi($auction, $this->prevPage, $this->auctions, "You do not have enough inventory space to purchase this!"));
				return;
			}

			/** @var null|SkyBlockPlayer $bidplayer */
			$bidplayer = $auction->getBidder()?->getPlayer();
			$bidder = $auction->getBidder();
			$prevbid = $auction->getBid();

			$bap = $player->getTechits();
			$bap2 = $bidplayer?->getTechits();
			$sti = $auction->buyNow($player);
			$am->removeAuction($auction);
			$aap = $player->getTechits();
			$aap2 = $bidplayer?->getTechits();
			$player->showModal(new AuctionSelectUi($this->auctions, $this->prevPage, "You received x" . $this->auction->getItem()->getCount() . " " . $auction->getItem()->getName() . " for " . TextFormat::AQUA . number_format($auction->getBuyNowPrice()) . " techits!", false));
			$type = Core::getInstance()->getNetwork()->getServerType();
			$outbid = [];
			if (!is_null($bidplayer) && $bidplayer->getXuid() !== $player->getXuid()) {
				if ($sti) $outbid[] = new Embed("", "rich", "**" . $bidplayer->getName() . "** was outbidded on **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bap2), true),
					new Field("After", number_format($aap2), true),
				]);
				else $outbid[] = new Embed("", "rich", "**" . $bidplayer->getName() . "** was outbidded on **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Amount", number_format($prevbid), true),
					new Field("Delivered To", "Inbox", true),
				]);
			} elseif (!is_null($bidder) && $bidder->getXuid() !== $player->getXuid() && !$sti) {
				$outbid[] = new Embed("", "rich", "**" . $bidder->getGamertag() . "** was outbidded on **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Amount", number_format($prevbid), true),
					new Field("Delivered To", "Inbox", true),
				]);
			}
			$post = new Post("", "AuctionHouse - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $player->getName() . "** just purchased **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "** for **" . number_format($auction->getBuyNowPrice()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bap), true),
					new Field("After", number_format($aap), true),
					new Field("Created By", $auction->getOwner()->getGamertag())
				]),
				...$outbid
			]);
			$post->setWebhook(Webhook::getWebhookByName("auctions-" . $type));
			$post->send();
			return;
		}
		$player->showModal(new SingleAuctionUi($auction, $this->prevPage));
	}
}
