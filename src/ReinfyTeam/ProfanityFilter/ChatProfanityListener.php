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
use function strtolower;

class ChatProfanityListener implements Listener {
	private Loader $pluginInstance;

	private string $filterMode;

	private string $profanityProvider;

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

		if (!$this->shouldFilter($player->hasPermission($this->pluginInstance->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"))) {
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

	private function resolveProfanityWords() : array {
		if (strtolower($this->profanityProvider) === Loader::PROVIDER_CUSTOM) {
			return (array) Loader::getInstance()->getProfanityConfig()->get("banned-words");
		}
		return (array) ($this->pluginInstance->getProvidedProfanityList() ?? ProfanityFilterService::getDefaultProfanityList());
	}

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

	private function applyHide(PlayerChatEvent $event, string $message, array $words, Player $player) : void {
		if ((bool) $this->pluginInstance->getConfig()->get("removeUnicode")) {
			$replacementCharacter = ($this->pluginInstance->getConfig()->get("replacementCharacter") ?? ProfanityFilterService::DEFAULT_REPLACEMENT_CHARACTER);
			$blockType = (int) ($this->pluginInstance->getConfig()->get("remove-unicode") ?? ProfanityFilterService::UNICODE_BLOCK_LETTERS);
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
		if (($this->pluginInstance->violationCounts[$playerName] ?? 0) === $this->pluginInstance->getConfig()->get("max-violations")) {
			$punishType = $this->pluginInstance->getConfig()->get("punishment-type");
			switch ($punishType) {
				case "ban":
					$this->pluginInstance->violationCounts[$playerName] = isset($this->pluginInstance->violationCounts[$playerName]);
					$player->getServer()->getNameBans()->addBan($playerName, "Profanity", $this->banDuration[0], $player->getServer()->getName());
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ned",
					]));
					$this->pluginInstance->getLogger()->warning($this->renderMessage("ban-warning-message", $player, $playerName));
					break;
				case "kick":
					$this->pluginInstance->violationCounts[$playerName] = isset($this->pluginInstance->violationCounts[$playerName]);
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ed",
					]));
					$this->pluginInstance->getLogger()->warning($this->renderMessage("kick-warning-message", $player, $playerName));
					break;
				case "command":
					$this->pluginInstance->violationCounts[$playerName] = isset($this->pluginInstance->violationCounts[$playerName]);
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

		$this->pluginInstance->violationCounts[$playerName] = isset($this->pluginInstance->violationCounts[$playerName]) ? $this->pluginInstance->violationCounts[$playerName] + 1 : 1;
	}

	private function renderMessage(string $configKey, ?\pocketmine\player\Player $player = null, ?string $playerName = null, array $extra = []) : string {
		return InfoAPI::render($this->pluginInstance, PluginUtils::colorize($this->pluginInstance->getConfig()->get($configKey)), [
			"player" => $player,
			"player_name" => $playerName ?? ($player?->getName() ?? ""),
			...$extra,
		], $player);
	}
}
