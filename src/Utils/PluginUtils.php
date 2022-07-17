<?php

/*
 *
 * __  __   __ _  __      __ | |_  __  __   ___    _ __
 * \ \/ /  / _` | \ \ /\ / / | __| \ \/ /  / _ \  | '_ \
 *  >  <  | (_| |  \ V  V /  | |_   >  <  | (_) | | | | |
 * /_/\_\  \__, |   \_/\_/    \__| /_/\_\  \___/  |_| |_|
 *            |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
 *
 */

declare(strict_types=1);

namespace xqwtxon\ProfanityFilter\Utils;

use pocketmine\utils\TextFormat;
use function str_replace;

final class PluginUtils {

	/**
	 * Colorise Messages turns & to § and etc.
	 */
	public static function colorize(string $message) : string {
		$message = str_replace("&", "§", $message);
		$message = str_replace("{BLACK}", TextFormat::BLACK, $message);
		$message = str_replace("{DARK_BLUE}", TextFormat::DARK_BLUE, $message);
		$message = str_replace("{DARK_GREEN}", TextFormat::DARK_GREEN, $message);
		$message = str_replace("{DARK_AQUA}", TextFormat::DARK_AQUA, $message);
		$message = str_replace("{DARK_RED}", TextFormat::DARK_RED, $message);
		$message = str_replace("{DARK_PURPLE}", TextFormat::DARK_PURPLE, $message);
		$message = str_replace("{GOLD}", TextFormat::GOLD, $message);
		$message = str_replace("{GRAY}", TextFormat::GRAY, $message);
		$message = str_replace("{DARK_GRAY}", TextFormat::DARK_GRAY, $message);
		$message = str_replace("{BLUE}", TextFormat::BLUE, $message);
		$message = str_replace("{GREEN}", TextFormat::GREEN, $message);
		$message = str_replace("{AQUA}", TextFormat::AQUA, $message);
		$message = str_replace("{RED}", TextFormat::RED, $message);
		$message = str_replace("{LIGHT_PURPLE}", TextFormat::LIGHT_PURPLE, $message);
		$message = str_replace("{YELLOW}", TextFormat::YELLOW, $message);
		$message = str_replace("{WHITE}", TextFormat::WHITE, $message);
		$message = str_replace("{OBFUSCATED}", TextFormat::OBFUSCATED, $message);
		$message = str_replace("{BOLD}", TextFormat::BOLD, $message);
		$message = str_replace("{STRIKETHROUGH}", TextFormat::STRIKETHROUGH, $message);
		$message = str_replace("{UNDERLINE}", TextFormat::UNDERLINE, $message);
		$message = str_replace("{ITALIC}", TextFormat::ITALIC, $message);
		$message = str_replace("{RESET}", TextFormat::RESET, $message);
		return $message;
	}
}
