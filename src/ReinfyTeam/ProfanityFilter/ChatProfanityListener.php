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

namespace ReinfyTeam\ProfanityFilter;

use Exception;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\InfoAPI;
use function filter_var;
use function in_array;
use function is_int;
use function is_string;
use function strlen;
use function strtolower;
use function trim;

class ChatProfanityListener implements Listener {
	private Loader $pluginInstance;

	/** @var Loader::FILTER_MODE_* */
	private string $filterMode;

	private string $profanityProvider;

	/** @var array{0: \DateTime, 1: string}|null */
	private ?array $banDuration;

	/**
	 * @param 'block'|'hide' $filterMode
	 */
	public function __construct(string $filterMode, string $profanityProvider) {
		$this->pluginInstance = Loader::getInstance();
		$this->filterMode = $filterMode;
		$this->banDuration = PluginUtils::getConfiguredDuration();
		$this->profanityProvider = $profanityProvider;
	}

	/**
	 * When player chat.
	 */
	public function onChat(PlayerChatEvent $event) : void {
		$message = $event->getMessage();
		$player = $event->getPlayer();

		if (!$this->shouldFilter($player)) {
			return;
		}

		$words = $this->resolveProfanityWords();
		if (!ProfanityFilterService::containsProfanity($message, $words)) {
			return;
		}
		$this->handleProfanity($event, $player, $message, $words);
	}

	private function shouldFilter(Player $player) : bool {
		if (!Loader::$isFilterEnabled) {
			return false;
		}

		return !$player->hasPermission($this->getBypassPermission());
	}

	private function getBypassPermission() : string {
		$rawBypass = $this->pluginInstance->getConfig()->get("bypass-permission");
		return is_string($rawBypass) ? $rawBypass : "profanityfilter.bypass";
	}

	/**
	 * @return string[]
	 */
	private function resolveProfanityWords() : array {
		if (strtolower($this->profanityProvider) === Loader::PROVIDER_CUSTOM) {
			/** @var string[] $custom */
			$custom = (array) Loader::getInstance()->getProfanityConfig()->get("banned-words");
			return $custom;
		}
		return $this->pluginInstance->getProvidedProfanityList();
	}

	/**
	 * @param string[] $words
	 */
	private function handleProfanity(PlayerChatEvent $event, Player $player, string $message, array $words) : void {
		switch ($this->filterMode) {
			case Loader::FILTER_MODE_BLOCK:
				$this->applyBlock($event, $player);
				break;
			case Loader::FILTER_MODE_HIDE:
				$this->applyHide($event, $message, $words, $player);
				break;
			default:
				throw new Exception("Cannot Identify the type of profanity in config.yml");
		}

		$this->handlePunishment($player);
	}

	private function applyBlock(PlayerChatEvent $event, Player $player) : void {
		$event->cancel();
		$player->sendMessage($this->renderMessage("block-message", $player));
		$this->pluginInstance->getLogger()->warning($this->renderMessage("block-warning-message", $player, $player->getName()));
	}

	/**
	 * @param string[] $words
	 */
	private function applyHide(PlayerChatEvent $event, string $message, array $words, Player $player) : void {
		$replacementCharacter = $this->resolveReplacementCharacter();
		$event->setMessage(ProfanityFilterService::maskProfanity($message, $words, $replacementCharacter));
		$this->pluginInstance->getLogger()->warning($this->renderMessage("hide-warning-message", $player, $player->getName()));
	}

	private function handlePunishment(Player $player) : void {
		$playerName = $player->getName();
		$maxViolations = $this->getMaxViolations();
		$currentCount = ($this->pluginInstance->violationCounts[$playerName] ?? 0) + 1;

		if ($currentCount >= $maxViolations) {
			$this->pluginInstance->violationCounts[$playerName] = 0;
			$this->applyConfiguredPunishment($player, $playerName);
			return;
		}

		$this->pluginInstance->violationCounts[$playerName] = $currentCount;
	}

	private function getMaxViolations() : int {
		$maxViolations = filter_var($this->pluginInstance->getConfig()->get("max-violations"), FILTER_VALIDATE_INT);
		if (!is_int($maxViolations) || $maxViolations < 1) {
			return 1;
		}

		return $maxViolations;
	}

	private function applyConfiguredPunishment(Player $player, string $playerName) : void {
		$punishType = $this->normalizePunishType();
		$banExpires = $this->banDuration[0] ?? null;

		switch ($punishType) {
			case "ban":
				$this->applyBan($player, $playerName, $banExpires);
				return;
			case "kick":
				$this->applyKick($player, $playerName);
				return;
			case "command":
				$this->applyCommandPunishment($player, $playerName);
				return;
			default:
				// This should never be reached due to normalization fallback.
				$this->applyKick($player, $playerName);
				return;
		}
	}

	private function applyBan(Player $player, string $playerName, ?\DateTime $banExpires) : void {
		$player->getServer()->getNameBans()->addBan($playerName, "Profanity", $banExpires, $player->getServer()->getName());
		$player->kick($this->renderMessage("kick-message", $player, $playerName, [
			"type" => "banned",
		]));
		$this->pluginInstance->getLogger()->warning($this->renderMessage("ban-warning-message", $player, $playerName));
	}

	private function applyKick(Player $player, string $playerName) : void {
		$player->kick($this->renderMessage("kick-message", $player, $playerName, [
			"type" => "kicked",
		]));
		$this->pluginInstance->getLogger()->warning($this->renderMessage("kick-warning-message", $player, $playerName));
	}

	private function applyCommandPunishment(Player $player, string $playerName) : void {
		$this->pluginInstance->getLogger()->warning($this->renderMessage("command-warning-message", $player, $playerName));
		$command = $this->renderMessage("command", $player, $playerName);

		if ((bool) $this->pluginInstance->getConfig()->get("execute-as-player")) {
			$this->pluginInstance->getServer()->dispatchCommand($player, $command);
			return;
		}

		$this->pluginInstance->getServer()->dispatchCommand(
			new ConsoleCommandSender($this->pluginInstance->getServer(), $this->pluginInstance->getServer()->getLanguage()),
			$command
		);
	}

	/**
	 * @param array<string, mixed> $extra
	 */
	private function renderMessage(string $configKey, ?Player $player = null, ?string $playerName = null, array $extra = []) : string {
		$configValue = $this->pluginInstance->getConfig()->get($configKey);
		$messageTemplate = is_string($configValue) ? $configValue : "";
		return InfoAPI::render($this->pluginInstance, PluginUtils::colorize($messageTemplate), [
			"player" => $player,
			"player_name" => $playerName ?? ($player?->getName() ?? ""),
			...$extra,
		], $player);
	}

	private function resolveReplacementCharacter() : string {
		$replacementConfig = $this->pluginInstance->getConfig()->get("replacementCharacter");
		$replacementCharacter = is_string($replacementConfig) ? $replacementConfig : "";
		if (strlen($replacementCharacter) !== 1) {
			$this->pluginInstance->getLogger()->warning("replacementCharacter must be exactly 1 character; falling back to default.");
			return ProfanityFilterService::DEFAULT_REPLACEMENT_CHARACTER;
		}

		return $replacementCharacter;
	}

	private function normalizePunishType() : string {
		$punishConfig = $this->pluginInstance->getConfig()->get("punishment-type");
		$punishType = is_string($punishConfig) ? strtolower(trim($punishConfig)) : "kick";
		if (!in_array($punishType, ["ban", "kick", "command"], true)) {
			$this->pluginInstance->getLogger()->warning("Invalid punishment-type '{$punishType}', defaulting to kick.");
			return "kick";
		}

		return $punishType;
	}
}