<?php

namespace skyblock\auctionhouse;

use pocketmine\item\{
	Durable,
	Item
};
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\techits\item\TechitNote;

use core\inbox\Inbox;
use core\inbox\object\{
	InboxInstance,
	MessageInstance
};
use core\session\mysqli\data\{
	MySqlRequest,
	MySqlQuery
};
use core\ui\elements\simpleForm\Button;
use core\user\User;
use core\utils\conversion\LegacyItemIds;
use core\utils\ItemRegistry;
use core\utils\TextFormat;

class Auction {

	const NO_BIDDER = 1111111111111111;

	public bool $deleted = false;

	public function __construct(
		public int $id,

		public User $owner,
		public int $created,

		public string $name,
		public Item $item,

		public int $startingbid = 0,
		public int $buynow = 0,

		public ?User $bidder = null,
		public int $bid = 0
	) {
	}

	public function tick(): bool {
		return $this->getLeft() > 0;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLeft(): int {
		return $this->getCreated() + (60 * 60 * 24) - time();
	}

	public function getFormattedTimeLeft(): string {
		$s = $this->getLeft();
		$hours = floor($s / 3600);
		$minutes = floor(((int) ($s / 60)) % 60);
		$seconds = $s % 60;

		return $hours . "h, " . $minutes . "m";
	}

	public function getOwner(): User {
		return $this->owner;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function getEncodedItem(): string {
		return base64_encode(serialize($this->getItem()->nbtSerialize()));
	}

	public function getStartingBid(): int {
		return $this->startingbid;
	}

	public function getBuyNowPrice(): int {
		return $this->buynow;
	}

	public function canBuyNow(Player $player): bool {
		/** @var SkyBlockPlayer $player */
		return $player->getTechits() >= $this->getBuyNowPrice();
	}

	public function getBid(): int {
		return $this->bid;
	}

	public function canBid(Player $player): bool {
		/** @var SkyBlockPlayer $player */
		return $player->getTechits() >= $this->getBid();
	}

	public function getBidder(): ?User {
		return $this->bidder;
	}

	public function getButton(): Button {
		$button = new Button($this->getName() . TextFormat::RESET . TextFormat::DARK_GRAY . PHP_EOL . number_format(($this->getBid() == 0 ? $this->getStartingBid() : $this->getBid())) . " - " . $this->getFormattedTimeLeft());
		$button->addImage("url", $this->getImage());
		return $button;
	}

	public function getImage(): string {
		$item = $this->getItem();
		return "[REDACTED]" . LegacyItemIds::typeIdToLegacyId($item->getTypeId()) . "-" . ($item instanceof Durable ? 0 : LegacyItemIds::stateIdToMeta($item)) . ".png";
	}

	public function setNewBidder(Player $player, int $bid): bool {
		/** @var SkyBlockPlayer $player */
		$player->takeTechits($bid);

		$old = $this->getBidder();
		$sentToInventory = true;
		if ($old !== null) {
			/** @var SkyBlockPlayer $pl */
			$pl = $old->getPlayer();
			if ($pl instanceof Player) {
				$pl->addTechits($this->getBid());
				$pl->sendMessage(TextFormat::RI . "You were outbidded on bid " . TextFormat::YELLOW . "'" . $this->getName() . TextFormat::RESET . TextFormat::YELLOW . "'");
			} else {
				$inbox = new InboxInstance($old, "here");
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Outbidded", "You were outbidded on bid " . TextFormat::YELLOW . $this->getName(), false);
				$note = ItemRegistry::TECHIT_NOTE();
				$note->setup(null, $this->getBid());
				$msg->setItems([$note]);
				$inbox->addMessage($msg, true);
				$sentToInventory = false;
			}
		}

		$this->bidder = $player->getUser();
		$this->bid = $bid;
		return $sentToInventory;
	}

	public function return(): bool {
		$bidder = $this->getBidder();
		if ($bidder !== null) {
			/** @var SkyBlockPlayer $pl */
			$pl = $bidder->getPlayer();
			if ($pl instanceof Player) {
				$pl->addTechits($this->getBid());
				$pl->sendMessage(TextFormat::RI . "Auction " . TextFormat::YELLOW . "'" . $this->getName() . TextFormat::RESET . TextFormat::YELLOW . "'" . TextFormat::GRAY . " was taken off the auction house, so your techits were returned!");
			} else {
				$inbox = new InboxInstance($bidder, "here");
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Auction removed", "Auction " . TextFormat::YELLOW . $this->getName() . TextFormat::WHITE . " was taken off the auction house, so your techits were returned!", false);
				$note = ItemRegistry::TECHIT_NOTE();
				$note->setup(null, $this->getBid());
				$msg->setItems([$note]);
				$inbox->addMessage($msg, true);
			}
		}

		$owner = $this->getOwner();
		if (($vp = $owner->validPlayer()) && ($player = $owner->getPlayer())->getInventory()->canAddItem($this->getItem())) {
			$player->sendMessage(TextFormat::RI . "Your auction " . TextFormat::YELLOW . "'" . $this->getName() . "'" . TextFormat::GRAY . " was returned to your inventory.");
			$player->getInventory()->addItem($this->getItem());
			return true;
		} else {
			if ($vp) {
				$player->sendMessage(TextFormat::RI . "Your auction " . TextFormat::YELLOW . "'" . $this->getName() . "'" . TextFormat::GRAY . " was returned to your inbox.");
				$inbox = $player->getSession()->getInbox()->getInbox(Inbox::TYPE_HERE);
			} else {
				$inbox = new InboxInstance($owner, "here");
			}
			$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Auction '" . $this->getName() . TextFormat::RESET . TextFormat::WHITE . "' returned", "Your auction item was returned to your inbox!", false);
			$msg->setItems([$this->getItem()]);
			$inbox->addMessage($msg, true);
			return false;
		}
	}

	public function buyNow(Player $player): bool {
		/** @var SkyBlockPlayer $player */
		$player->takeTechits($this->getBuyNowPrice());
		$player->getInventory()->addItem($this->getItem());

		$old = $this->getBidder();
		$sentToInventory = true;
		if ($old !== null) {
			/** @var SkyBlockPlayer $pl */
			$pl = $old->getPlayer();
			if ($pl instanceof Player) {
				$pl->addTechits($this->getBid());
				$pl->sendMessage(TextFormat::RI . "You were outbidded on bid " . TextFormat::YELLOW . "'" . $this->getName() . TextFormat::RESET . TextFormat::YELLOW . "'");
			} else {
				$inbox = new InboxInstance($old, "here");
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Outbidded", "You were outbidded on auction " . TextFormat::YELLOW . $this->getName(), false);
				$note = ItemRegistry::TECHIT_NOTE();
				$note->setup(null, $this->getBid());
				$msg->setItems([$note]);
				$inbox->addMessage($msg, true);
				$sentToInventory = false;
			}
		}

		$owner = $this->getOwner();
		/** @var SkyBlockPlayer $pl */
		$pl = $owner->getPlayer();
		if ($pl instanceof Player && $pl->isLoaded()) {
			$pl->addTechits($this->getBuyNowPrice());
			$pl->sendMessage(TextFormat::GI . "Your auction " . TextFormat::YELLOW . "'" . $this->getName() . "'" . TextFormat::RESET . TextFormat::GRAY . " has been bought by " . TextFormat::AQUA . $player->getName() . "!");
		} else {
			$inbox = new InboxInstance($owner, "here");
			$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Auction Bought", "Your auction " . TextFormat::YELLOW . $this->getName() . TextFormat::WHITE . " was bought by " . TextFormat::AQUA . $player->getName(), false);
			$note = ItemRegistry::TECHIT_NOTE();
			$note->setup(null, $this->getBuyNowPrice());
			$msg->setItems([$note]);
			$inbox->addMessage($msg, true);
		}

		return $sentToInventory;
	}

	public function reward(): bool {
		$bidder = $this->getBidder();
		if ($bidder !== null) {
			if ($bidder->validPlayer()) $bidder->getPlayer()->sendMessage(TextFormat::RI . "You won auction " . TextFormat::YELLOW . "'" . $this->getName() . TextFormat::RESET . TextFormat::YELLOW . "'!" . TextFormat::GRAY . " The item has been sent to your inbox.");
			$inbox = new InboxInstance($bidder, "here");
			$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Won Auction #" . $this->getId(), "Congratulations! You had the highest bid on auction " . TextFormat::YELLOW . $this->getName() . "!", false);
			$msg->setItems([$this->getItem()]);
			$inbox->addMessage($msg, true);

			$owner = $this->getOwner();
			/** @var SkyBlockPlayer $pl */
			$pl = $owner->getPlayer();
			if ($pl instanceof Player && $pl->isLoaded()) {
				$pl->addTechits($this->getBid());
				$pl->sendMessage(TextFormat::GI . "Your auction " . TextFormat::YELLOW . "'" . $this->getName() . "'" . TextFormat::RESET . TextFormat::GRAY . " has been claimed by " . TextFormat::AQUA . $bidder->getGamertag() . "!");
			} else {
				$inbox = new InboxInstance($owner, "here");
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Auction Bought", "Your auction " . TextFormat::YELLOW . $this->getName() . TextFormat::WHITE . " was claimed by " . TextFormat::AQUA . $bidder->getGamertag(), false);
				$note = ItemRegistry::TECHIT_NOTE();
				$note->setup(null, $this->getBid());
				$msg->setItems([$note]);
				$inbox->addMessage($msg, true);
			}

			return true;
		}
		$owner = $this->getOwner();
		if ($owner->validPlayer() && $owner->getPlayer()->isLoaded()) {
			($op = $owner->getPlayer())->sendMessage(TextFormat::RI . "Your auction " . TextFormat::YELLOW . "'" . $this->getName() . "'" . TextFormat::GRAY . " has expired! You can retrieve from your inbox.");
			$inbox = $op->getSession()->getInbox()->getInbox(Inbox::TYPE_HERE);
		} else {
			$inbox = new InboxInstance($owner, "here");
		}
		$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Auction '" . $this->getName() . TextFormat::RESET . TextFormat::WHITE . "' expired", "No one bidded on your auction, and your item was returned.", false);
		$msg->setItems([$this->getItem()]);
		$inbox->addMessage($msg, true);
		return false;
	}

	public function delete(): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"delete_auction_" . $this->getId(),
			new MySqlQuery("main", "DELETE FROM auctions WHERE xuid=? AND created=?", [$this->getOwner()->getXuid(), $this->getCreated()])
		), function (MySqlRequest $request): void {
			$this->deleted = true;
		});
	}
}
