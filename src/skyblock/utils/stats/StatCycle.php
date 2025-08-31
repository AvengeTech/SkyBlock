<?php

namespace skyblock\utils\stats;

use skyblock\SkyBlock;
use skyblock\techits\item\TechitNote;

use core\Core;
use core\discord\objects\{
	Post,
	Webhook,
	Embed,
	Footer
};
use core\inbox\object\{
	InboxInstance,
	MessageInstance
};
use core\network\protocol\ServerSubUpdatePacket;
use core\network\server\SubServer;
use core\session\mysqli\data\{
	MySqlRequest,
	MySqlQuery
};
use core\utils\ItemRegistry;

class StatCycle {

	const TYPE_WEEKLY = 0;
	const TYPE_MONTHLY = 1;

	public function __construct() {
		foreach (
			[
				//"DROP TABLE IF EXISTS stat_cycle",
				"CREATE TABLE IF NOT EXISTS stat_cycle(type INT NOT NULL, date VARCHAR(12) NOT NULL)"
			] as $query
		) {
			SkyBlock::getInstance()->getSessionManager()->getDatabase()->query($query);
		}

		$monthlyFunc = function (bool $canReset): void {
			if ($canReset) {
				$this->runMonthlyCycle(function (): void {
					$this->resetMonthlyStats(function (): void {
					});
				});
				return;
			}
		};
		if (date("D") === "Sun") {
			$this->checkLastWeeklyCycle(function (bool $canReset) use ($monthlyFunc): void {
				if ($canReset) {
					$this->runWeeklyCycle(function () use ($monthlyFunc): void {
						$this->resetWeeklyStats(function () use ($monthlyFunc): void {
							if (date("d") == 1) $this->checkLastMonthlyCycle($monthlyFunc);
						});
					});
				} else {
					if (date("d") == 1) $this->checkLastMonthlyCycle($monthlyFunc);
				}
			});
		} elseif (date("d") == 1) {
			$this->checkLastMonthlyCycle($monthlyFunc);
		}
	}

	public function checkLastWeeklyCycle(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"stat_check_weekly",
			new MySqlQuery("main", "SELECT * FROM stat_cycle WHERE type=? AND date=?", [self::TYPE_WEEKLY, date("m/d/y")])
		), function (MySqlRequest $request) use ($onCompletion): void {
			$onCompletion(count($request->getQuery()->getResult()->getRows()) === 0);
		});
	}

	public function runWeeklyCycle(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_check", [
			new MySqlQuery(
				"combat_kills_weekly",
				"SELECT xuid, kills FROM combat_stats WHERE ttype=1 ORDER BY kills DESC LIMIT 5"
			),
			new MySqlQuery(
				"koth_kills_weekly",
				"SELECT xuid, kills FROM koth_stats WHERE ttype=1 ORDER BY kills DESC LIMIT 5"
			),
			new MySqlQuery(
				"koth_wins_weekly",
				"SELECT xuid, wins FROM koth_stats WHERE ttype=1 ORDER BY wins DESC LIMIT 5"
			),
		]), function (MySqlRequest $request) use ($onCompletion): void {
			$combat_kills = $request->getQuery("combat_kills_weekly")->getResult()->getRows();
			$koth_kills = $request->getQuery("koth_kills_weekly")->getResult()->getRows();
			$koth_wins = $request->getQuery("koth_wins_weekly")->getResult()->getRows();
			$xuids = [];
			foreach ($combat_kills as $row) $xuids[] = $row["xuid"];
			foreach ($koth_kills as $row) $xuids[] = $row["xuid"];
			foreach ($koth_wins as $row) $xuids[] = $row["xuid"];
			Core::getInstance()->getUserPool()->useUsers($xuids, function (array $users) use ($onCompletion, $combat_kills, $koth_kills, $koth_wins): void {
				$combatKills = [];
				foreach ($combat_kills as $key => $data) {
					$place = $key + 1;
					$combatKills[($user = $users[$data["xuid"]])->getGamertag()] = $kills = $data["kills"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Weekly warzone kill leaderboard!", "You were #" . $place . " on the weekly warzone kill leaderboard last week with " . $kills . " kills, congratulations! " . ($place !== 1 ? "Score higher on the leaderboard next week for a bigger prize!" : ""), false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 100000;
							break;
						case 2:
							$value = 50000;
							break;
						case 3:
							$value = 25000;
							break;
						case 4:
							$value = 10000;
							break;
						case 5:
							$value = 5000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$combatK = "Warzone weekly top kills:" . PHP_EOL;
				$prizeTotals = [
					1 => 100000,
					2 => 50000,
					3 => 25000,
					4 => 10000,
					5 => 5000
				];
				$place = 1;
				foreach ($combatKills as $gamertag => $kills) {
					$combatK .= $place . ". " . $gamertag . " - " . $kills . " kills [" . number_format($prizeTotals[$place]) . " techits]" . PHP_EOL;
					$place++;
				}
				$combatK = rtrim($combatK);

				$kothKills = [];
				foreach ($koth_kills as $key => $data) {
					$place = $key + 1;
					$kothKills[($user = $users[$data["xuid"]])->getGamertag()] = $kills = $data["kills"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Weekly KOTH kill leaderboard!", "You were #" . $place . " on the weekly KOTH kill leaderboard last week with " . $kills . " kills, congratulations! " . ($place !== 1 ? "Score higher on the leaderboard next week for a bigger prize!" : ""), false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 100000;
							break;
						case 2:
							$value = 50000;
							break;
						case 3:
							$value = 25000;
							break;
						case 4:
							$value = 10000;
							break;
						case 5:
							$value = 5000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$kothK = "KOTH weekly top kills:" . PHP_EOL;
				$prizeTotals = [
					1 => 100000,
					2 => 50000,
					3 => 25000,
					4 => 10000,
					5 => 5000
				];
				$place = 1;
				foreach ($kothKills as $gamertag => $kills) {
					$kothK .= $place . ". " . $gamertag . " - " . $kills . " kills [" . number_format($prizeTotals[$place]) . " techits]" . PHP_EOL;
					$place++;
				}
				$kothK = rtrim($kothK);

				$kothWins = [];
				foreach ($koth_wins as $key => $data) {
					$place = $key + 1;
					$kothWins[($user = $users[$data["xuid"]])->getGamertag()] = $wins = $data["wins"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Weekly KOTH win leaderboard!", "You were #" . $place . " on the weekly KOTH win leaderboard last week with " . $wins . " wins, congratulations! " . ($place !== 1 ? "Score higher on the leaderboard next week for a bigger prize!" : ""), false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 500000;
							break;
						case 2:
							$value = 250000;
							break;
						case 3:
							$value = 100000;
							break;
						case 4:
							$value = 50000;
							break;
						case 5:
							$value = 25000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$kothW = "KOTH weekly top wins:" . PHP_EOL;
				$prizeTotals = [
					1 => 500000,
					2 => 250000,
					3 => 100000,
					4 => 50000,
					5 => 25000
				];
				$place = 1;
				foreach ($kothWins as $gamertag => $wins) {
					$kothW .= $place . ". " . $gamertag . " - " . $wins . " wins [" . number_format($prizeTotals[$place]) . " techits]" . PHP_EOL;
					$place++;
				}
				$kothW = rtrim($kothW);

				if (!Core::thisServer()->isTestServer()) {
					$server = Core::thisServer();
					if ($server->isSubServer()) {
						/** @var SubServer $server */
						$server = $server->getParentServer()->getIdentifier();
					} else {
						$server = $server->getIdentifier();
					}
					$post = new Post(
						"",
						"Weekly Stats - " . $server,
						"[REDACTED]",
						false,
						"",
						[
							new Embed(
								"",
								"rich",
								"Weekly stats reset! All weekly leaderboards are now clear and ready to be filled again, get on the leaderboard for a chance to earn a prize!" . PHP_EOL . PHP_EOL .
									$combatK . PHP_EOL . PHP_EOL .
									$kothK . PHP_EOL . PHP_EOL .
									$kothW,
								"",
								"ffb106",
								new Footer("Reset date: " . date("F j, Y, g:ia", time())),
								"",
								"[REDACTED]",
								null,
								[]
							)
						]
					);
					$post->setWebhook(Webhook::getWebhookByName("stats"));
					$post->send();
				}

				SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_weekly_log", new MySqlQuery(
					"main",
					"INSERT INTO stat_cycle(type, date) VALUES(?, ?)",
					[self::TYPE_WEEKLY, date("m/d/y")]
				)), function (MySqlRequest $request) use ($onCompletion): void {
					$onCompletion();
				});
			});
		});
	}

	public function resetWeeklyStats(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_weekly_reset", [
			new MySqlQuery("reset_combat", "UPDATE combat_stats SET kills=0, deaths=0, supply_drops=0, money_bags=0, mobs=0 WHERE ttype=1"),
			new MySqlQuery("reset_koth", "UPDATE koth_stats SET kills=0, deaths=0, wins=0 WHERE ttype=1"),
		]), function (MySqlRequest $request) use ($onCompletion): void {
			$servers = [];
			foreach (Core::thisServer()->getSubServers(false, true) as $server) {
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "weekly"
			]))->queue();

			$onCompletion();
		});
	}

	public function checkLastMonthlyCycle(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"stat_check",
			new MySqlQuery("main", "SELECT * FROM stat_cycle WHERE type=? AND date=?", [self::TYPE_MONTHLY, date("m/y", time())])
		), function (MySqlRequest $request) use ($onCompletion): void {
			$onCompletion(count($request->getQuery()->getResult()->getRows()) == 0);
		});
	}

	public function runMonthlyCycle(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_check_monthly", [
			new MySqlQuery(
				"combat_kills_monthly",
				"SELECT xuid, kills FROM combat_stats WHERE ttype=2 ORDER BY kills DESC LIMIT 5"
			),
			new MySqlQuery(
				"koth_kills_monthly",
				"SELECT xuid, kills FROM koth_stats WHERE ttype=2 ORDER BY kills DESC LIMIT 5"
			),
			new MySqlQuery(
				"koth_wins_monthly",
				"SELECT xuid, wins FROM koth_stats WHERE ttype=2 ORDER BY wins DESC LIMIT 5"
			),
		]), function (MySqlRequest $request) use ($onCompletion): void {
			$combat_kills = $request->getQuery("combat_kills_monthly")->getResult()->getRows();
			$koth_kills = $request->getQuery("koth_kills_monthly")->getResult()->getRows();
			$koth_wins = $request->getQuery("koth_wins_monthly")->getResult()->getRows();
			$xuids = [];
			foreach ($combat_kills as $row) $xuids[] = $row["xuid"];
			foreach ($koth_kills as $row) $xuids[] = $row["xuid"];
			foreach ($koth_wins as $row) $xuids[] = $row["xuid"];
			Core::getInstance()->getUserPool()->useUsers($xuids, function (array $users) use ($onCompletion, $combat_kills, $koth_kills, $koth_wins): void {
				$prizeMsg = [
					1 => "[1,000,000 techits + $10 PayPal]",
					2 => "[1,000,000 techits + $10 store credit]",
					3 => "[750,000 techits]",
					4 => "[500,000 techits]",
					5 => "[250,000 techits]",
				];
				$combatKills = [];
				foreach ($combat_kills as $key => $data) {
					$place = $key + 1;
					$combatKills[($user = $users[$data["xuid"]])->getGamertag()] = $kills = $data["kills"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Monthly Warzone kill leaderboard!", "You were #" . $place . " on the monthly Warzone kill leaderboard last week with " . $kills . " kills, congratulations! " . PHP_EOL . PHP_EOL . "Prizes: " . $prizeMsg[$place] . PHP_EOL . PHP_EOL . "Did you win a PayPal or store credit prize? Open a ticket in our discord server with proof of this message to claim your prize!", false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 1000000;
							break;
						case 2:
							$value = 1000000;
							break;
						case 3:
							$value = 750000;
							break;
						case 4:
							$value = 500000;
							break;
						case 5:
							$value = 250000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$combatK = "Warzone monthly top kills:" . PHP_EOL;
				$place = 1;
				foreach ($combatKills as $gamertag => $kills) {
					$combatK .= $place . ". " . $gamertag . " - " . $kills . " kills " . $prizeMsg[$place] . PHP_EOL;
					$place++;
				}
				$combatK = rtrim($combatK);

				$prizeMsg = [
					1 => "[1,000,000 techits + $10 PayPal]",
					2 => "[1,000,000 techits + $10 store credit]",
					3 => "[750,000 techits]",
					4 => "[500,000 techits]",
					5 => "[250,000 techits]",
				];
				$kothKills = [];
				foreach ($koth_kills as $key => $data) {
					$place = $key + 1;
					$kothKills[($user = $users[$data["xuid"]])->getGamertag()] = $kills = $data["kills"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Monthly KOTH kill leaderboard!", "You were #" . $place . " on the monthly KOTH kill leaderboard last week with " . $kills . " kills, congratulations! " . PHP_EOL . PHP_EOL . "Prizes: " . $prizeMsg[$place] . PHP_EOL . PHP_EOL . "Did you win a PayPal or store credit prize? Open a ticket in our discord server with proof of this message to claim your prize!", false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 1000000;
							break;
						case 2:
							$value = 1000000;
							break;
						case 3:
							$value = 750000;
							break;
						case 4:
							$value = 500000;
							break;
						case 5:
							$value = 250000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$kothK = "KOTH monthly top kills:" . PHP_EOL;
				$place = 1;
				foreach ($kothKills as $gamertag => $kills) {
					$kothK .= $place . ". " . $gamertag . " - " . $kills . " kills " . $prizeMsg[$place] . PHP_EOL;
					$place++;
				}
				$kothK = rtrim($kothK);

				$prizeMsg = [
					1 => "[2,500,000 techits + $10 PayPal]",
					2 => "[1,000,000 techits + $10 store credit]",
					3 => "[750,000 techits]",
					4 => "[500,000 techits]",
					5 => "[250,000 techits]",
				];
				$kothWins = [];
				foreach ($koth_wins as $key => $data) {
					$place = $key + 1;
					$kothWins[($user = $users[$data["xuid"]])->getGamertag()] = $wins = $data["wins"];
					$inbox = new InboxInstance($user, "here");
					$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Monthly KOTH win leaderboard!", "You were #" . $place . " on the monthly KOTH win leaderboard last week with " . $wins . " wins, congratulations! " . PHP_EOL . PHP_EOL . "Prizes: " . $prizeMsg[$place] . PHP_EOL . PHP_EOL . "Did you win a PayPal or store credit prize? Open a ticket in our discord server with proof of this message to claim your prize!", false);
					$note = ItemRegistry::TECHIT_NOTE();
					$value = 0;
					switch ($place) {
						case 1:
							$value = 2500000;
							break;
						case 2:
							$value = 1000000;
							break;
						case 3:
							$value = 750000;
							break;
						case 4:
							$value = 500000;
							break;
						case 5:
							$value = 250000;
							break;
					}
					$note->setup(null, $value);
					$msg->setItems([$note]);
					$inbox->addMessage($msg, true);
				}
				$kothW = "KOTH monthly top wins:" . PHP_EOL;
				$place = 1;
				foreach ($kothWins as $gamertag => $wins) {
					$kothW .= $place . ". " . $gamertag . " - " . $wins . " wins " . $prizeMsg[$place] . PHP_EOL;
					$place++;
				}
				$kothW = rtrim($kothW);

				if (!Core::thisServer()->isTestServer()) {
					$server = Core::thisServer();
					if ($server->isSubServer()) {
						/** @var SubServer $server */
						$server = $server->getParentServer()->getIdentifier();
					} else {
						$server = $server->getIdentifier();
					}
					$post = new Post("", "Monthly Stats - " . $server, "[REDACTED]", false, "", [
						new Embed(
							"",
							"rich",
							"Monthly stats reset! All monthly leaderboards are now clear and ready to be filled again, get on the leaderboard for a chance to earn a prize!" . PHP_EOL . PHP_EOL .
								$combatK . PHP_EOL . PHP_EOL .
								$kothK . PHP_EOL . PHP_EOL .
								$kothW . PHP_EOL . PHP_EOL .
								"Did you win a PayPal or store credit prize? Open a questions ticket to redeem it!",
							"",
							"ffb106",
							new Footer("Reset date: " . date("F j, Y, g:ia", time())),
							"",
							"[REDACTED]",
							null,
							[]
						)
					]);
					$post->setWebhook(Webhook::getWebhookByName("stats"));
					$post->send();
				}

				SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_weekly_log", new MySqlQuery(
					"main",
					"INSERT INTO stat_cycle(type, date) VALUES(?, ?)",
					[self::TYPE_MONTHLY, date("m/y")]
				)), function (MySqlRequest $request) use ($onCompletion): void {
					$onCompletion();
				});
			});
		});
	}

	public function resetMonthlyStats(\Closure $onCompletion): void {
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("stat_monthly_reset", [
			new MySqlQuery("reset_combat", "UPDATE combat_stats SET kills=0, deaths=0, supply_drops=0, money_bags=0, mobs=0 WHERE ttype=2"),
			new MySqlQuery("reset_koth", "UPDATE koth_stats SET kills=0, deaths=0, wins=0 WHERE ttype=2"),
		]), function (MySqlRequest $request) use ($onCompletion): void {
			$servers = [];
			foreach (Core::thisServer()->getSubServers(false, true) as $server) {
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "monthly"
			]))->queue();

			$onCompletion();
		});
	}
}
