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
use function is_array;
use function is_string;
use function ltrim;
use function preg_match_all;
use function preg_replace;
use function str_replace;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;

final class PluginUtils {
	private const FOREVER_BAN_VALUE = "Forever";

	/** @var string[] */
	public const FALLBACK_CUSTOM_WORDS = [
		"f4ck",
		"f@ck",
		"fuk",
		"b1tch",
		"b!tch",
		"sh!t",
		"sh1tty",
		"a55",
		"a5shole",
		"c0cks",
	];

	/** @var array<string, string> */
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

	public static function colorize(string $message) : string {
		return str_replace(array_keys(self::COLOR_REPLACEMENTS), array_values(self::COLOR_REPLACEMENTS), $message);
	}

	/**
	 * Convert a duration string like "1d2h" into a DateTime and leftover text.
	 * The leftover text is returned to preserve any trailing reason string.
	 *
	 * @return array{0: DateTime, 1: string}|null
	 */
	private static function parseDurationString(string $durationString) : ?array {
		if (trim($durationString) === "") {
			return null;
		}

		$dateTime = new DateTime();
		preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $durationString, $matches);
		if (count($matches[0]) < 1) {
			return null;
		}

		$amounts = preg_replace("/[^0-9]/", "", $matches[0]);
		if (!is_array($amounts)) {
			return null;
		}
		foreach ($amounts as $index => $amount) {
			$unit = $matches[1][$index] ?? "";
			$dateTime->add(new DateInterval(self::buildIntervalSpec($amount, $unit)));
			$durationString = str_replace($matches[0][$index], "", $durationString);
		}

		return [$dateTime, ltrim(str_replace($matches[0], "", $durationString))];
	}

	private static function buildIntervalSpec(string $amount, string $unit) : string {
		switch ($unit) {
			case "y":
			case "w":
			case "d":
				return "P" . $amount . strtoupper($unit);
			case "mo":
				return "P" . $amount . strtoupper(substr($unit, 0, strlen($unit) - 1));
			case "h":
			case "m":
			case "s":
				return "PT" . $amount . strtoupper($unit);
			default:
				return "PT" . $amount . "S";
		}
	}

	/**
	 * @return array{0: DateTime, 1: string}|null
	 */
	public static function getConfiguredDuration() : ?array {
		$configValue = Loader::getInstance()->getConfig()->get("ban-duration");
		if ($configValue === self::FOREVER_BAN_VALUE) {
			return null;
		}

		return is_string($configValue) ? self::parseDurationString($configValue) : null;
	}

	/**
	 * Make sure custom profanity words never collide with the provided default list.
	 *
	 * @param string[] $fallbackWords
	 */
	public static function sanitizeCustomProfanityList(array $fallbackWords = self::FALLBACK_CUSTOM_WORDS) : void {
		$config = Loader::getInstance()->getProfanityConfig();
		$rawWords = $config->get("banned-words");
		$providedLookup = self::buildLookup(Loader::getInstance()->getProvidedProfanityList());

		$filtered = [];
		$seen = [];
		$changed = false;

		foreach ((array) $rawWords as $word) {
			$changed = self::accumulateValidWord($word, $providedLookup, $seen, $filtered) || $changed;
		}

		if ($filtered === []) {
			$changed = self::applyFallbackWords($fallbackWords, $providedLookup, $seen, $filtered) || $changed;
		}

		if ($changed) {
			$config->set("banned-words", array_values($filtered));
			$config->save();
			$config->reload();
		}
	}

	/**
	 * @param array<string, bool> $providedLookup
	 * @param array<string, bool> $seen
	 * @param string[] $filtered
	 */
	private static function accumulateValidWord(mixed $word, array $providedLookup, array &$seen, array &$filtered) : bool {
		if (!is_string($word)) {
			return true;
		}
		$normalized = self::normalizeWord($word);
		if ($normalized === "") {
			return true;
		}
		if (isset($providedLookup[$normalized]) || isset($seen[$normalized])) {
			return true;
		}

		$seen[$normalized] = true;
		$filtered[] = $word;
		return false;
	}

	/**
	 * @param string[] $fallbackWords
	 * @param array<string, bool> $providedLookup
	 * @param array<string, bool> $seen
	 * @param string[] $filtered
	 */
	private static function applyFallbackWords(array $fallbackWords, array $providedLookup, array &$seen, array &$filtered) : bool {
		$changed = false;
		foreach ($fallbackWords as $fallback) {
			if (!is_string($fallback)) {
				continue;
			}
			$normalized = self::normalizeWord($fallback);
			if ($normalized === "" || isset($providedLookup[$normalized]) || isset($seen[$normalized])) {
				continue;
			}
			$filtered[] = $fallback;
			$seen[$normalized] = true;
			$changed = true;
		}

		return $changed;
	}

	public static function removeProfanityWord(string $word) : bool {
		/** @var string[] $words */
		$words = (array) Loader::getInstance()->getProfanityConfig()->get("banned-words");
		$newArray = array_diff($words, [$word]);
		Loader::getInstance()->getProfanityConfig()->set("banned-words", array_values($newArray));
		Loader::getInstance()->getProfanityConfig()->save();
		Loader::getInstance()->getProfanityConfig()->reload();
		return true;
	}

	public static function addProfanityWord(string $word) : bool {
		/** @var string[] $words */
		$words = (array) Loader::getInstance()->getProfanityConfig()->get("banned-words");
		$providedLookup = self::buildLookup(Loader::getInstance()->getProvidedProfanityList());

		$filteredWords = [];
		$customLookup = [];
		foreach ($words as $existingWord) {
			if (!is_string($existingWord)) {
				continue;
			}
			$filteredWords[] = $existingWord;
			$customLookup[self::normalizeWord($existingWord)] = true;
		}

		$normalized = self::normalizeWord($word);
		if ($normalized === "") {
			return false;
		}

		if (isset($providedLookup[$normalized]) || isset($customLookup[$normalized])) {
			return false;
		}

		$filteredWords[] = $word;
		Loader::getInstance()->getProfanityConfig()->set("banned-words", $filteredWords);
		Loader::getInstance()->getProfanityConfig()->save();
		Loader::getInstance()->getProfanityConfig()->reload();
		return true;
	}

	/**
	 * @param string[] $words
	 * @return array<string, bool>
	 */
	private static function buildLookup(array $words) : array {
		$lookup = [];
		foreach ($words as $word) {
			if (!is_string($word)) {
				continue;
			}
			$normalized = self::normalizeWord($word);
			if ($normalized === "") {
				continue;
			}
			$lookup[$normalized] = true;
		}

		return $lookup;
	}

	private static function normalizeWord(string $word) : string {
		return strtolower(trim($word));
	}
}
