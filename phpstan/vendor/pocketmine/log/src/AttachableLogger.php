<?php

/*
 * PocketMine Standard PHP Library
 * Copyright (C) 2014-2018 PocketMine Team <https://github.com/PocketMine/PocketMine-SPL>
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

/**
 * @phpstan-type LoggerAttachment \Closure(mixed $level, string $message) : void
 */
interface AttachableLogger extends \Logger{

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 *
	 * @return void
	 */
	public function addAttachment(\Closure $attachment);

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 *
	 * @return void
	 */
	public function removeAttachment(\Closure $attachment);

	/**
	 * @return void
	 */
	public function removeAttachments();

	/**
	 * @return \Closure[]
	 * @phpstan-return LoggerAttachment[]
	 */
	public function getAttachments();
}
