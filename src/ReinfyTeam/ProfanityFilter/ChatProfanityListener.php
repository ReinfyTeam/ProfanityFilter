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
use SOFe\InfoAPI\InfoAPI;
use function filter_var;
use function is_int;
use function is_string;
use function strtolower;

class ChatProfanityListener implements Listener {
	private Loader $pluginInstance;

	private string $filterMode;

	private string $profanityProvider;

	/** @var array{0: \DateTime, 1: string}|null */
	private ?array $banDuration;

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

		$rawBypass = $this->pluginInstance->getConfig()->get("bypass-permission");
		$bypassPermission = is_string($rawBypass) ? $rawBypass : "profanityfilter.bypass";
		if (!$this->shouldFilter($player->hasPermission($bypassPermission))) {
			return;
		}

		$words = $this->resolveProfanityWords();
		if (!ProfanityFilterService::containsProfanity($message, $words)) {
			return;
		}
		$this->handleProfanity($event, $player, $message, $words);
	}

	private function shouldFilter(bool $hasBypass) : bool {
		if (!Loader::$isFilterEnabled) {
			return false;
		}
		return !$hasBypass;
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
		if ((bool) $this->pluginInstance->getConfig()->get("removeUnicode")) {
			$replacementConfig = $this->pluginInstance->getConfig()->get("replacementCharacter");
			$replacementCharacter = is_string($replacementConfig) ? $replacementConfig : ProfanityFilterService::DEFAULT_REPLACEMENT_CHARACTER;
			$blockTypeConfig = $this->pluginInstance->getConfig()->get("remove-unicode");
			$blockType = is_int($blockTypeConfig) ? $blockTypeConfig : ProfanityFilterService::UNICODE_BLOCK_LETTERS;
			$useMultibyteLength = (bool) ($this->pluginInstance->getConfig()->get("mb-strlen") ?? false);
			$event->setMessage(
				ProfanityFilterService::sanitizeUnicode(
					ProfanityFilterService::maskProfanity($message, $words, $replacementCharacter),
					$blockType,
					$useMultibyteLength
				)
			);
		} else {
			$event->setMessage(ProfanityFilterService::maskProfanity($message, $words));
		}
		$this->pluginInstance->getLogger()->warning($this->renderMessage("hide-warning-message", $player, $player->getName()));
	}

	private function handlePunishment(Player $player) : void {
		$playerName = $player->getName();
		$maxViolations = filter_var($this->pluginInstance->getConfig()->get("max-violations"), FILTER_VALIDATE_INT);
		if (!is_int($maxViolations)) {
			$maxViolations = 0;
		}
		if (($this->pluginInstance->violationCounts[$playerName] ?? 0) === $maxViolations) {
			$punishConfig = $this->pluginInstance->getConfig()->get("punishment-type");
			$punishType = is_string($punishConfig) ? $punishConfig : "kick";
			$banExpires = $this->banDuration[0] ?? null;
			switch ($punishType) {
				case "ban":
					$this->pluginInstance->violationCounts[$playerName] = 0;
					$player->getServer()->getNameBans()->addBan($playerName, "Profanity", $banExpires, $player->getServer()->getName());
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ned",
					]));
					$this->pluginInstance->getLogger()->warning($this->renderMessage("ban-warning-message", $player, $playerName));
					break;
				case "kick":
					$this->pluginInstance->violationCounts[$playerName] = 0;
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ed",
					]));
					$this->pluginInstance->getLogger()->warning($this->renderMessage("kick-warning-message", $player, $playerName));
					break;
				case "command":
					$this->pluginInstance->violationCounts[$playerName] = 0;
					$this->pluginInstance->getLogger()->warning($this->renderMessage("command-warning-message", $player, $playerName));
					if ((bool) $this->pluginInstance->getConfig()->get("execute-as-player")) {
						$this->pluginInstance->getServer()->dispatchCommand($player, $this->renderMessage("command", $player, $playerName));
					} else {
						$this->pluginInstance->getServer()->dispatchCommand(new ConsoleCommandSender($this->pluginInstance->getServer(), $this->pluginInstance->getServer()->getLanguage()), $this->renderMessage("command", $player, $playerName));
					}
					break;
				default:
					throw new Exception("Cannot Identify the type of punishment in config.yml!");
			}
			return;
		}

		$currentCount = $this->pluginInstance->violationCounts[$playerName] ?? 0;
		$this->pluginInstance->violationCounts[$playerName] = $currentCount + 1;
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
}
