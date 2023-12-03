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
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ProfanityFilter\Loader;
use function array_keys;
use function array_values;
use function count;
use function is_bool;
use function ltrim;
use function preg_match_all;
use function preg_replace;
use function str_replace;
use function strlen;
use function strtoupper;
use function strval;
use function substr;
use function trim;

final class PluginUtils {
	/**
	 * Colorise Messages turns & to § and etc.
	 */
	public static function colorize(string $message) : string {
		$replacements = [
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
		$message = str_replace(array_keys($replacements), array_values($replacements), $message);
		return $message;
	}

	public static function assumeNotFalse(mixed $given, string $message = "This line should be not false. PLEASE REPORT THIS TO THE DEVELOPER.", bool $invert = false) {
		if (is_bool($given)) {
			if (!$given) {
				throw new \RuntimeException($message); // assume not false ;(
			}
		}
	}

	/**
	 * Convert String to Timestamp
	 *
	 * @return ?array
	 */
	private static function stringToTimestamp(string $string) : ?array {
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
		if (trim($string) === "") {
			return null;
		}
		$t = new DateTime();
		preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $string, $found);
		if (count($found[0]) < 1) {
			return null;
		}
		$found[2] = preg_replace("/[^0-9]/", "", $found[0]);
		foreach ($found[2] as $k => $i) {
			switch ($c = $found[1][$k]) {
				case "y":
				case "w":
				case "d":
					$t->add(new DateInterval("P" . $i . strtoupper($c)));
					break;
				case "mo":
					$t->add(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
					break;
				case "h":
				case "m":
				case "s":
					$t->add(new DateInterval("PT" . $i . strtoupper($c)));
					break;
				default:
					$t->add(new DateInterval("PT" . $i . "S"));
					break;
			}
			$string = str_replace($found[0][$k], "", $string);
		}
		return [$t, ltrim(str_replace($found[0], "", $string))];
	}

	public static function getDuration() {
		if (Loader::getInstance()->getConfig()->get("ban-duration") === "Forever") {
			return null;
		} else {
			return self::stringToTimestamp(Loader::getInstance()->getConfig()->get("ban-duration"));
		}
	}
	
	public static function removeProfanityWord(string $word) : bool{
		$words = Loader::getInstance()->getProfanity()->get("banned-words");
		$newArray = array_diff($words, array($word));
		Loader::getInstance()->getProfanity()->set("banned-words", (array) array_values($newArray));
		Loader::getInstance()->getProfanity()->save();
		Loader::getInstance()->getProfanity()->reload();
		return true;
	}
	
	public static function addProfanityWord(string $word) :bool {
		$words = Loader::getInstance()->getProfanity()->get("banned-words");
		$words[] = $word;
		Loader::getInstance()->getProfanity()->set("banned-words", (array) $words);
		Loader::getInstance()->getProfanity()->save();
		Loader::getInstance()->getProfanity()->reload();
		return true;
	}
}
