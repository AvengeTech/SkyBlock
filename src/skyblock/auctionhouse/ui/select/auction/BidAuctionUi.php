<?php

namespace skyblock\auctionhouse\ui\select\auction;

use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\player\Player;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{Label, Input};

use skyblock\auctionhouse\Auction;
use skyblock\auctionhouse\ui\select\AuctionSelectUi;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\utils\TextFormat;

class BidAuctionUi extends CustomForm {

	public function __construct(public Auction $auction, public int $prevPage = 1, public array $auctions = [], string $message = "", bool $error = true) {
		parent::__construct("Bidding...");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"You are bidding on this auction. You must type a value higher than " . TextFormat::AQUA . number_format(($auction->getBid() == 0 ? $auction->getStartingBid() : $auction->getBid())) . " techits" . TextFormat::WHITE . " (Current bid value)"
		));
		$this->addElement(new Input("Bid Value", "Techit value"));
		$this->addElement(new Label("By pressing submit, you will bid on this auction. This will take however many techits from your balance and put you as the highest bidder."));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$am = SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager();
		$auction = $am->getAuctionByAuction($this->auction);

		if ($auction == null) {
			$player->showModal(new AuctionSelectUi($this->auctions, $this->prevPage, "This auction has expired! If you are the last bidder of this auction, it should appear in your Auction Bin."));
			return;
		}

		$value = (int) $response[1];
		$bid = ($auction->getBid() == 0 ? $auction->getStartingBid() : $auction->getBid());

		if ($value <= $bid) {
			$player->showModal(new BidAuctionUi($auction, $this->prevPage, $this->auctions, "Value entered is lower than current auction bid or the bid has recently increased!"));
			return;
		}
		if ($player->getTechits() < $value) {
			$player->showModal(new SingleAuctionUi($auction, $this->prevPage, $this->auctions, "You do not have enough techits to bid!"));
			return;
		}

		/** @var null|SkyBlockPlayer $bidplayer */
		$bidplayer = $auction->getBidder()?->getPlayer();
		$bidder = $auction->getBidder();
		$prevbid = $auction->getBid();

		$bbid = $player->getTechits();
		$bbid2 = $bidplayer?->getTechits();
		$sti = $auction->setNewBidder($player, $value);
		$abid = $player->getTechits();
		$abid2 = $bidplayer?->getTechits();
		$player->showModal(new SingleAuctionUi($auction, $this->prevPage, $this->auctions, "You bidded " . TextFormat::AQUA . number_format($value) . " techits" . TextFormat::GREEN . " on this item!", false));
		$type = Core::getInstance()->getNetwork()->getServerType();
		$outbid = [];
		if (!is_null($bidplayer) && $bidplayer->getXuid() !== $player->getXuid()) {
			if ($sti) $outbid[] = new Embed("", "rich", "**" . $bidplayer->getName() . "** was outbidded on **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($bbid2), true),
				new Field("After", number_format($abid2), true),
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
			new Embed("", "rich", "**" . $player->getName() . "** just bid **" . number_format($value) . "** on **x" . $this->auction->getItem()->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($bbid), true),
				new Field("After", number_format($abid), true),
				new Field("Created By", $auction->getOwner()->getGamertag())
			]),
			...$outbid
		]);
		$post->setWebhook(Webhook::getWebhookByName("auctions-" . $type));
		$post->send();
	}
}
