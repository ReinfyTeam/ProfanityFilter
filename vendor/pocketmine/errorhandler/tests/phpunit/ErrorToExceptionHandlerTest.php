<?php

/*
 * PocketMine Standard PHP Library
 * Copyright (C) 2019 PocketMine Team <https://github.com/pmmp/PocketMine-SPL>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

declare(strict_types=1);

namespace pocketmine\errorhandler;

use PHPUnit\Framework\TestCase;
use function error_reporting;
use function file_get_contents;

class ErrorToExceptionHandlerTest extends TestCase{

	public function testTrapNormal() : void{
		$this->expectException(\ErrorException::class);
		ErrorToExceptionHandler::trap(fn() => file_get_contents('/i dont exist'));
	}

	/**
	 * Silence operator must not interfere with trap(), otherwise it can lead to unpredictable behaviour.
	 */
	public function testTrapWithSilence() : void{
		$this->expectException(\ErrorException::class);
		@ErrorToExceptionHandler::trap(fn() => file_get_contents('/i dont exist'));
	}

	/**
	 * error_reporting() must also not interfere with trap().
	 */
	public function testTrapWithErrorReporting() : void{
		$this->expectException(\ErrorException::class);
		error_reporting(0);
		ErrorToExceptionHandler::trap(fn() => file_get_contents('/i dont exist'));
	}
}