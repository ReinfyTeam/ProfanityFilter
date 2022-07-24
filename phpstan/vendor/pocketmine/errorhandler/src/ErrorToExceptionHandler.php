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

use function error_reporting;
use function set_error_handler;

final class ErrorToExceptionHandler{
	private function __construct(){

	}

	/** @var ErrorRecord|null */
	private static $lastSilencedError = null;

	/**
	 * @param int    $severity
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 *
	 * @return bool
	 * @throws \ErrorException
	 */
	public static function handle(int $severity, string $message, string $file, int $line) : bool{
		if((error_reporting() & $severity) !== 0){
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}

		self::$lastSilencedError = new ErrorRecord($severity, $message, $file, $line);
		return true; //stfu operator
	}

	public static function getLastSilencedError() : ErrorRecord{
		if(self::$lastSilencedError === null){
			throw new \LogicException("No error has been generated");
		}
		return self::$lastSilencedError;
	}

	public static function clearLastSilencedError() : void{
		self::$lastSilencedError = null;
	}

	public static function getAndClearLastSilencedError() : ErrorRecord{
		$result = self::getLastSilencedError();
		self::clearLastSilencedError();
		return $result;
	}

	/**
	 * Shorthand method to set the error-to-exception error handler.
	 */
	public static function set() : void{
		set_error_handler([self::class, 'handle']);
	}
}
