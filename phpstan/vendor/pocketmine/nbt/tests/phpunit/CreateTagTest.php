<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class CreateTagTest extends TestCase{

	/**
	 * Test that all known tag types can be deserialized
	 *
	 * @throws \Exception
	 */
	public function testCreateTags() : void{
		$root = new TreeRoot(CompoundTag::create()
			->setByte("byte", 1)
			->setShort("short", 1)
			->setInt("int", 1)
			->setLong("long", 1)
			->setFloat("float", 1)
			->setDouble("double", 1)
			->setByteArray("bytearray", "\x01")
			->setString("string", "string")
			->setTag("list", new ListTag([new ByteTag(1)]))
			->setIntArray("intarray", [1]), "compound");

		$dat = (new BigEndianNbtSerializer())->write($root);
		$root2 = (new BigEndianNbtSerializer())->read($dat);

		self::assertTrue($root->equals($root2));
	}
}
