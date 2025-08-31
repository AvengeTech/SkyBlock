<?php namespace skyblock\islands\world\provider;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\utils\Binary;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\WorldCreationOptions;

class IslandWorldProvider implements WritableWorldProvider{

	private string $path;
	/** @var array<int,Chunk,ChunkData> */
	protected array $chunks = [];

	public function __construct(string $path){
		$this->path = rtrim($path, "/\\");
		$this->load();
	}

	public static function isValid(string $path) : bool {
		$path = rtrim($path, "/\\");
		return str_ends_with($path, ".island") && file_exists($path) && is_file($path);
	}

	public static function generate(string $path, string $name, WorldCreationOptions $options) : void{
		// NOOP
	}


	public function getWorldMinY() : int{
		return 0;
	}

	public function getWorldMaxY() : int{
		return 256;
	}

	public function getPath() : string{
		return $this->path;
	}

	public function loadChunk(int $chunkX, int $chunkZ) : ?LoadedChunkData{
		return $this->chunks[self::chunkIndex($chunkX, $chunkZ)] ?? null;
	}

	public function doGarbageCollection() : void{
	}

	public function getWorldData() : WorldData{
		return new IslandWorldData();
	}

	public function close() : void{
		$this->save();
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator{
		foreach($this->chunks as $chunk){
			yield $chunk;
		}
	}

	public function calculateChunkCount() : int{
		return count($this->chunks);
	}

	public function saveChunk(int $chunkX, int $chunkZ, ChunkData $chunkData, int $dirtFlags) : void{
		$empty = count($chunkData->getEntityNBT()) === 0 && count($chunkData->getTileNBT()) === 0;
		if($empty){
			foreach($chunkData->getSubChunks() as $subChunk){
				if(!$subChunk->isEmptyAuthoritative()){
					$empty = false;
					break;
				}
			}
		}
		if($empty){
			unset($this->chunks[self::chunkIndex($chunkX, $chunkZ)]);
			return;
		}

		$this->chunks[self::chunkIndex($chunkX, $chunkZ)] = $chunkData;
	}

	protected function load() : void{
		$this->chunks = [];

		$file = $this->path;
		if(!file_exists($file)){
			return;
		}

		$stream = new IslandBinaryStream(file_get_contents($file));
		try{
			$stream->getMagic();
			$stream->getVersion();

			$minX = $stream->getSignedShort();
			$minZ = $stream->getSignedShort();
			$width = $stream->getShort();
			$depth = $stream->getShort();

			$populatedChunks = $stream->getBitSet($width * $depth, ceil(($width * $depth) / 8));

			$chunkData = new IslandBinaryStream($stream->getCompressed());
			//$tileData = $stream->getCompressedCompound();
			//$entityData = $stream->getCompressedCompound();
		}catch(\Exception $e){
			throw new CorruptedWorldException($e->getMessage());
		}

		$idMap = LegacyBlockIdToStringIdMap::getInstance();
		$reverse = [];
		foreach($idMap->getLegacyToStringMap() as $k => $v) $reverse[$v] = $k;
		$stringToLegacy = function(string $id) use ($reverse): ?int {
			if (!isset($reverse[$id])) return null;
			return $reverse[$id];
		};
		foreach($populatedChunks as $index => $populated){
			if(!$populated){
				continue;
			}
			$x = $index % $width + $minX;
			$z = $index / $width + $minZ;

			$heightMap = [];
			$heightMapLength = $chunkData->getLInt();
			for($i = 0; $i < $heightMapLength; $i++){
				$heightMap[] = $chunkData->getShort();
			}
			$biomeArray = $chunkData->getString();
			$biomeIds = new PalettedBlockArray($biomeArray);

			$populatedSubChunks = $chunkData->getBitSet(16, 2);
			$subChunks = [];
			foreach($populatedSubChunks as $subChunkIndex => $populatedSubChunk){
				if(!$populatedSubChunk){
					continue;
				}

				$skyLight = $chunkData->getLightArray();
				$blockLight = $chunkData->getLightArray();

				$layers = [];
				$layersLength = $chunkData->getByte();
				for($i = 0; $i < $layersLength; $i++){
					$bitsPerBlock = $chunkData->getByte() >> 1;
					try{
						$words = $chunkData->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
					}catch(\InvalidArgumentException $e){
						throw new CorruptedChunkException("Failed to deserialize paletted storage: " . $e->getMessage(), 0, $e);
					}

					$palette = [];
					$paletteLength = $chunkData->getLInt();
					for($j = 0; $j < $paletteLength; $j++){
						$id = $stringToLegacy($chunkData->getString()) ?? BlockTypeIds::INFO_UPDATE;
						$palette[] = ($id << Block::INTERNAL_STATE_DATA_BITS) | $chunkData->getByte();
					}
					$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
				}

				$subChunks[$subChunkIndex] = new SubChunk(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS, $layers, $biomeIds, $skyLight, $blockLight);
			}

			$chunk = new Chunk($subChunks, true);
			$chunk->setHeightMapArray($heightMap);
			$this->chunks[self::chunkIndex($x, $z)] = [$chunk, new ChunkData($chunk->getSubChunks(), $chunk->isPopulated(), [], [])]; // TODO: Tiles and entities
		}
	}

	protected function save() : void{
		$stream = new IslandBinaryStream();

		$stream->putMagic();
		$stream->putVersion();

		$totalChunks = count($this->chunks);
		$minX = $totalChunks > 0 ? PHP_INT_MAX : 0;
		$minZ = $totalChunks > 0 ? PHP_INT_MAX : 0;
		$maxX = $totalChunks > 0 ? PHP_INT_MIN : 0;
		$maxZ = $totalChunks > 0 ? PHP_INT_MIN : 0;
		foreach(array_keys($this->chunks) as $index){
			[$x, $z] = self::chunkXZ($index);
			$minX = min($minX, $x);
			$minZ = min($minZ, $z);
			$maxX = max($maxX, $x);
			$maxZ = max($maxZ, $z);
		}
		$stream->putShort($minX);
		$stream->putShort($minZ);
		$stream->putShort(($width = $maxX - $minX + 1));
		$stream->putShort(($depth = $maxZ - $minZ + 1));

		$populatedChunks = array_fill(0, $width * $depth, false);
		foreach($this->chunks as $index => $chunk){
			[$x, $z] = self::chunkXZ($index);

			$chunkIndex = ($z - $minZ) * $width + ($x - $minX);
			$populatedChunks[$chunkIndex] = true;
		}
		$stream->putBitSet($populatedChunks, ceil(($width * $depth) / 8));

		$idMap = LegacyBlockIdToStringIdMap::getInstance();
		$uncompressedChunkData = new IslandBinaryStream();
		for($z = $minZ; $z <= $maxZ; $z++){
			for($x = $minX; $x <= $maxX; $x++){
				/** @var Chunk */
				$chunkData = ($this->chunks[self::chunkIndex($x, $z)] ?? [null])[0];
				if($chunkData === null) {
					continue;
				}
				$chunk = $chunkData;

				$uncompressedChunkData->putLInt(count($chunk->getHeightMapArray()));
				foreach($chunk->getHeightMapArray() as $height){
					$uncompressedChunkData->putShort($height);
				}
				$uncompressedChunkData->putString($chunk->getSubChunk(0)->getBiomeArray()->getWordArray());

				$populatedSubChunks = array_fill(0, 16, false);
				foreach($chunk->getSubChunks() as $subChunkY => $subChunk){
					$empty = $subChunk->isEmptyAuthoritative();
					$populatedSubChunks[$subChunkY] = !$empty;
				}
				$uncompressedChunkData->putBitSet($populatedSubChunks, 2);

				foreach($chunk->getSubChunks() as $i => $subChunk){
					if(!$populatedSubChunks[$i]) {
						continue;
					}

					$uncompressedChunkData->putLightArray($subChunk->getBlockSkyLightArray());
					$uncompressedChunkData->putLightArray($subChunk->getBlockLightArray());

					$layers = $subChunk->getBlockLayers();
					$uncompressedChunkData->putByte(count($layers));
					foreach($layers as $blocks){
						if($blocks->getBitsPerBlock() !== 0) {
							$uncompressedChunkData->putByte($blocks->getBitsPerBlock() << 1);
							$uncompressedChunkData->put($blocks->getWordArray());
						} else {
							$uncompressedChunkData->putByte(1 << 1);
							$uncompressedChunkData->put(str_repeat("\x00", PalettedBlockArray::getExpectedWordArraySize(1)));
						}

						$palette = $blocks->getPalette();
						$uncompressedChunkData->putLInt(count($palette));
						foreach($palette as $p){
							$uncompressedChunkData->putString($idMap->legacyToString($p >> Block::INTERNAL_STATE_DATA_BITS) ?? "minecraft:info_update");
							$uncompressedChunkData->putByte($p & Block::INTERNAL_STATE_DATA_BITS);
						}
					}
				}
			}
		}
		$stream->putCompressed($uncompressedChunkData->getBuffer());

		//$uncompressedTileData = CompoundTag::create();
		//$stream->putCompressedCompound($uncompressedTileData);

		//$uncompressedEntityData = CompoundTag::create();
		//$stream->putCompressedCompound($uncompressedEntityData);

		file_put_contents($this->path, $stream->getBuffer());
	}

	public static function chunkIndex(int $chunkX, int $chunkZ) : string{
		return Binary::writeLInt($chunkX) . Binary::writeLInt($chunkZ);
	}

	public static function chunkXZ(string $index) : array{
		return [Binary::readLInt(substr($index, 0, 4)), Binary::readLInt(substr($index, 4, 4))];
	}
}

/*class MethodLogger {
	private $parent;
	private array $log = [];

	public function __construct($parent) {
		$this->parent = $parent;
	}

	public function visualize(): string {
		return implode("\n", array_map(fn(array $entry) => $entry[0] . ($entry[1] > 1 ? " x" . $entry[1] : ""), $this->log));
	}

	public function addDebug(string $debug): void {
		$this->log[] = [$debug, 1];
	}

	public function __call($method, $arguments) {
		$retVal = call_user_func_array([$this->parent, $method], $arguments);

		$i = count($this->log) - 1;
		if($i >= 0) {
			if($this->log[$i][0] === $method) {
				$this->log[$i][1]++;
				return $retVal;
			}
		}
		$this->log[] = [$method, 1];
		return $retVal;
	}
}*/
