<?php namespace skyblock\auctionhouse;

use skyblock\SkyBlock;
use skyblock\auctionhouse\command\AuctionHouseCommand;

use core\Core;

class AuctionHouse{

	public AuctionManager $auctionManager;

	public function __construct(public SkyBlock $plugin){
		foreach([
			//"DROP TABLE IF EXISTS auctions",
			"CREATE TABLE IF NOT EXISTS auctions(
				xuid BIGINT(16) NOT NULL,
				created INT NOT NULL,
				name VARCHAR(55) NOT NULL DEFAULT 'My Auction',
				item BLOB NOT NULL,
				startingbid INT NOT NULL DEFAULT 0, buynow INT NOT NULL DEFAULT 0,
				bidder BIGINT(16) NOT NULL DEFAULT 0, bid INT NOT NULL DEFAULT 0,
				PRIMARY KEY(xuid, created)
			)"
		] as $query) $plugin->getSessionManager()->getDatabase()->query($query);

		$this->auctionManager = new AuctionManager();

		$plugin->getServer()->getCommandMap()->register("auctionhouse", new AuctionHouseCommand($plugin, "auctionhouse", "Opens the Auction House"));
	}

	public function getAuctionManager() : AuctionManager{
		return $this->auctionManager;
	}

	public function tick() : void{
		if(!Core::thisServer()->isSubServer()){
			$this->getAuctionManager()->tick();
		}
	}

	public function close() : void{
		if(!Core::thisServer()->isSubServer())
			$this->getAuctionManager()->close();
	}

}