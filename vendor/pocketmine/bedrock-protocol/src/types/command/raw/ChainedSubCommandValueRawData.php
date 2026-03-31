<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\command\raw;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;

final class ChainedSubCommandValueRawData{

	public function __construct(
		private int $nameIndex,
		private int $type
	){}

	public function getNameIndex() : int{ return $this->nameIndex; }

	public function getType() : int{ return $this->type; }

	public static function read(ByteBufferReader $in) : self{
		$nameIndex = VarInt::readUnsignedInt($in);
		$type = VarInt::readUnsignedInt($in);

		return new self($nameIndex, $type);
	}

	public function write(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, $this->nameIndex);
		VarInt::writeUnsignedInt($out, $this->type);
	}
}
