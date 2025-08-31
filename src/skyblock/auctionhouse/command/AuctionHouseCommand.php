<?php

namespace skyblock\auctionhouse\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\Core;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\auctionhouse\ui\MainAuctionUi;
use skyblock\auctionhouse\ui\select\AuctionSelectUi;

class AuctionHouseCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["ah"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if (Core::thisServer()->isSubServer()) {
			$sender->sendMessage(TextFormat::RI . "Auction house can only be used at spawn!");
			return;
		}
		if(count($args) == 0){
			$sender->showModal(new MainAuctionUi());
			return;
		}
		$auctions = SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager()->getPlayerAuctions($args[0]);
		if(count($auctions) == 0){
			$sender->sendMessage(TextFormat::RI . "This player has no auctions available!");
			return;
		}
		$sender->showModal(new AuctionSelectUi($auctions));
	}
}