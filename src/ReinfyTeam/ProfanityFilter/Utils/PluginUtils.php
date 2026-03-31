<?php

/*
 *
 *  ____           _            __           _____
 * |  _ \    ___  (_)  _ __    / _|  _   _  |_   _|   ___    __ _   _ __ ___
 * | |_) |  / _ \ | | | '_ \  | |_  | | | |   | |    / _ \  / _` | | '_ ` _ \
 * |  _ <  |  __/ | | | | | | |  _| | |_| |   | |   |  __/ | (_| | | | | | | |
 * |_| \_\  \___| |_| |_| |_| |_|    \__, |   |_|    \___|  \__,_| |_| |_| |_|
 *                                   |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ReinfyTeam
 * @link https://github.com/ReinfyTeam/
 *
 *
 */

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\Utils;

use DateInterval;
use DateTime;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ProfanityFilter\Loader;
use function array_diff;
use function array_keys;
use function array_values;
use function count;
use function ltrim;
use function preg_match_all;
use function preg_replace;
use function str_replace;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

final class PluginUtils {
	private const FOREVER_BAN_VALUE = "Forever";

	private const COLOR_REPLACEMENTS = [
		"&" => "§",
		"{BLACK}" => TextFormat::BLACK,
		"{DARK_BLUE}" => TextFormat::DARK_BLUE,
		"{DARK_GREEN}" => TextFormat::DARK_GREEN,
		"{DARK_AQUA}" => TextFormat::DARK_AQUA,
		"{DARK_RED}" => TextFormat::DARK_RED,
		"{DARK_PURPLE}" => TextFormat::DARK_PURPLE,
		"{GOLD}" => TextFormat::GOLD,
		"{GRAY}" => TextFormat::GRAY,
		"{DARK_GRAY}" => TextFormat::DARK_GRAY,
		"{BLUE}" => TextFormat::BLUE,
		"{GREEN}" => TextFormat::GREEN,
		"{AQUA}" => TextFormat::AQUA,
		"{RED}" => TextFormat::RED,
		"{LIGHT_PURPLE}" => TextFormat::LIGHT_PURPLE,
		"{YELLOW}" => TextFormat::YELLOW,
		"{WHITE}" => TextFormat::WHITE,
		"{OBFUSCATED}" => TextFormat::OBFUSCATED,
		"{BOLD}" => TextFormat::BOLD,
		"{STRIKETHROUGH}" => TextFormat::STRIKETHROUGH,
		"{UNDERLINE}" => TextFormat::UNDERLINE,
		"{ITALIC}" => TextFormat::ITALIC,
		"{RESET}" => TextFormat::RESET,
	];
	/**
	 * Colorise Messages turns & to § and etc.
	 */
	public static function colorize(string $message) : string {
		return str_replace(array_keys(self::COLOR_REPLACEMENTS), array_values(self::COLOR_REPLACEMENTS), $message);
	}

	/**
	 * Convert String to Timestamp
	 */
	private static function parseDurationString(string $durationString) : ?array {
		/**
		 * Rules:
		 * Integers without suffix are considered as seconds
		 * "s" is for seconds
		 * "m" is for minutes
		 * "h" is for hours
		 * "d" is for days
		 * "w" is for weeks
		 * "mo" is for months
		 * "y" is for years
		 */
		if (trim($durationString) === "") {
			return null;
		}
		$dateTime = new DateTime();
		preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $durationString, $matches);
		if (count($matches[0]) < 1) {
			return null;
		}
		$matches[2] = preg_replace("/[^0-9]/", "", $matches[0]);
		foreach ($matches[2] as $index => $amount) {
			$unit = $matches[1][$index];
			switch ($unit) {
				case "y":
				case "w":
				case "d":
					$dateTime->add(new DateInterval("P" . $amount . strtoupper($unit)));
					break;
				case "mo":
					$dateTime->add(new DateInterval("P" . $amount . strtoupper(substr($unit, 0, strlen($unit) - 1))));
					break;
				case "h":
				case "m":
				case "s":
					$dateTime->add(new DateInterval("PT" . $amount . strtoupper($unit)));
					break;
				default:
					$dateTime->add(new DateInterval("PT" . $amount . "S"));
					break;
			}
			$durationString = str_replace($matches[0][$index], "", $durationString);
		}
		return [$dateTime, ltrim(str_replace($matches[0], "", $durationString))];
	}

	public static function getConfiguredDuration() {
		if (Loader::getInstance()->getConfig()->get("ban-duration") === self::FOREVER_BAN_VALUE) {
			return null;
		} else {
			return self::parseDurationString(Loader::getInstance()->getConfig()->get("ban-duration"));
		}
	}

	public static function removeProfanityWord(string $word) : bool {
		$words = Loader::getInstance()->getProfanityConfig()->get("banned-words");
		$newArray = array_diff($words, [$word]);
		Loader::getInstance()->getProfanityConfig()->set("banned-words", (array) array_values($newArray));
		Loader::getInstance()->getProfanityConfig()->save();
		Loader::getInstance()->getProfanityConfig()->reload();
		return true;
	}

	public static function addProfanityWord(string $word) : bool {
		$words = Loader::getInstance()->getProfanityConfig()->get("banned-words");
		$words[] = $word;
		Loader::getInstance()->getProfanityConfig()->set("banned-words", (array) $words);
		Loader::getInstance()->getProfanityConfig()->save();
		Loader::getInstance()->getProfanityConfig()->reload();
		return true;
	}
}






