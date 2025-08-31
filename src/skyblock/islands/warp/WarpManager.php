<?php

namespace skyblock\islands\warp;

use pocketmine\block\Block;

use skyblock\islands\Island;

class WarpManager {

	const WARPS_PER_LEVEL = 3;

	public function __construct(
		public Island $island,
		public array $warps = [],
		public array $warpPads = [],
	) {
	}

	public function save(bool $async = true): void {
		foreach ($this->getWarps() as $warp) {
			$warp->save($async);
		}
		foreach ($this->getWarpPads() as $warpPad) {
			$warpPad->save($async);
		}
	}

	/**
	 * Used if island is loaded before island world
	 */
	public function resetLocations(): void {
		foreach ($this->getWarps() as $warp) {
			$warp->updateLocation(
				$warp->getLocation()->getX(),
				$warp->getLocation()->getY(),
				$warp->getLocation()->getZ(),
				$warp->getLocation()->getYaw()
			);
		}
	}

	public function getIsland(): Island {
		return $this->island;
	}

	public function getWarpLimit(): int {
		return $this->getIsland()->getSizeLevel() * self::WARPS_PER_LEVEL;
	}

	public function getWarps(): array {
		return $this->warps;
	}

	public function getWarpsFor(int $hierarchy): array {
		$warps = [];
		foreach ($this->getWarps() as $warp) {
			if ($warp->getHierarchy() <= $hierarchy) $warps[$warp->getName()] = $warp;
		}
		return $warps;
	}

	public function getWarp(string $name): ?Warp {
		return $this->warps[$name] ?? null;
	}

	public function addWarp(Warp $warp): void {
		$this->warps[$warp->getName()] = $warp;
	}

	public function removeWarp(string $name, bool $delete = true): void {
		$warp = $this->warps[$name] ?? null;
		if ($warp !== null) {
			unset($this->warps[$name]);
			if ($delete) {
				$warp->delete();
			}
		}
	}

	public function getWarpPads(): array {
		return $this->warpPads;
	}

	public function getWarpPadByBlock(Block $block): ?WarpPad {
		$key = ($pos = $block->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();

		return $this->warpPads[$key] ?? null;
	}

	public function addWarpPad(WarpPad $warpPad): void {
		$this->warpPads[$warpPad->getKey()] = $warpPad;
	}

	public function removeWarpPad(WarpPad $warpPad): void {
		$warp = $this->warpPads[$warpPad->getKey()] ?? null;
		if ($warp !== null) {
			unset($this->warpPads[$warpPad->getKey()]);
			$warpPad->delete();
		}
		unset($this->warpPads[$warpPad->getKey()]);
	}

	public function delete(): void {
		foreach ($this->getWarps() as $name => $warp) {
			$this->removeWarp($name);
		}
	}
}
