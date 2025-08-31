<?php namespace skyblock\islands\world\provider;

use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\LightArray;
use pocketmine\world\WorldException;

class IslandBinaryStream extends BinaryStream{

	public const MAGIC = 0xFCB8;
	public const VERSION = 0x01;

	public function getMagic() : void{
		$magic = $this->getShort();
		if($magic !== self::MAGIC){
			throw new WorldException("World contains incorrect magic, have 0x" . dechex($magic) . ", expected 0x" . dechex(self::MAGIC));
		}
	}

	public function putMagic() : void{
		$this->putShort(self::MAGIC);
	}

	public function getVersion() : void{
		$version = $this->getByte();
		if($version !== self::VERSION){
			throw new WorldException("World contains unsupported version, have " . $version . ", expected " . self::VERSION);
		}
	}

	public function putVersion() : void{
		$this->putByte(self::VERSION);
	}

	/**
	 * @param int $length
	 * @return bool[]
	 */
	public function getBitSet(int $length, int $mask) : array{
		$bits = [];
		for($i = 0; $i < $length; $i++){
			$bits[$i] = $this->getByte() !== 0;
		}
		for($i = 0; $i < $mask - count($bits); $i++){
			$this->getByte();
		}
		return $bits;
	}

	/**
	 * @param bool[] $bits
	 * @param int    $fixedLength
	 * @return void
	 */
	public function putBitSet(array $bits, int $mask) : void{
		foreach($bits as $bit){
			$this->putByte($bit ? 1 : 0);
		}
		for($i = 0; $i < $mask - count($bits); $i++){
			$this->putByte(0);
		}
	}

	public function getCompressed() : string{
		$compressedLength = $this->getLInt();
		$uncompressedLength = $this->getLInt();

		$compressedData = $this->get($compressedLength);
		$uncompressedData = zlib_decode($compressedData);

		if(strlen($uncompressedData) !== $uncompressedLength){
			throw new CorruptedWorldException("Length of uncompressed data does not match expected length");
		}
		return $uncompressedData;
	}

	public function putCompressed(string $data) : void{
		$compressedData = zlib_encode($data, ZLIB_ENCODING_DEFLATE, 9);

		$this->putLInt(strlen($compressedData));
		$this->putLInt(strlen($data));
		$this->put($compressedData);
	}

	public function getCompressedCompound() : CompoundTag{
		$data = $this->getCompressed();

		$offset = $this->getOffset();
		try{
			$tag = (new NetworkNbtSerializer())->read($data, $offset, 512);
			if (!$tag instanceof CompoundTag) throw new NbtDataException("Expected type " . CompoundTag::class . ", found " . $tag::class);
			$this->setOffset($offset);
			return $tag;
		}catch(NbtDataException $e){
			throw new CorruptedWorldException("Failed decoding compressed compound: " . $e->getMessage());
		}
	}

	public function putCompressedCompound(CompoundTag $tag) : void{
		$data = (new NetworkNbtSerializer())->write(new TreeRoot($tag));
		$this->putCompressed($data);
	}

	public function getString() : string{
		return $this->get($this->getShort());
	}

	public function putString(string $v) : void{
		$this->putShort(strlen($v));
		$this->put($v);
	}

	public function getLightArray() : LightArray{
		return new LightArray($this->getString());
	}

	public function putLightArray(LightArray $light) : void{
		$this->putString($light->getData());
	}
}