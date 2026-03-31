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

class EventListener implements Listener {
	private Loader $plugin;

	private string $type;

	private string $provider;

	private ?array $duration;

	public function __construct(string $type, string $provider) {
		$this->plugin = Loader::getInstance();
		$this->type = $type;
		$this->duration = PluginUtils::getDuration();
		$this->provider = $provider;
	}

	/**
	 * When player chat.
	 */
	public function onChat(PlayerChatEvent $event) : void {
		$message = $event->getMessage();
		$player = $event->getPlayer();

		if (!$this->shouldFilter($player->hasPermission($this->plugin->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"))) {
			return;
		}

		$words = $this->resolveProfanityWords();
		if (!PluginAPI::detectProfanity($message, $words)) {
			return;
		}
		$this->handleProfanity($event, $player, $message, $words);
	}

	private function shouldFilter(bool $hasBypass) : bool {
		if (!Loader::$enabled) {
			return false;
		}
		return !$hasBypass;
	}

	private function resolveProfanityWords() : array {
		if (strtolower($this->provider) === "custom") {
			return (array) Loader::getInstance()->getProfanity()->get("banned-words");
		}
		return (array) ($this->plugin->getProvidedProfanities() ?? PluginAPI::defaultProfanity());
	}

	private function handleProfanity(PlayerChatEvent $event, Player $player, string $message, array $words) : void {
		switch ($this->type) {
			case "block":
				$this->applyBlock($event, $player);
				break;
			case "hide":
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
		$this->plugin->getLogger()->warning($this->renderMessage("block-warning-message", $player, $player->getName()));
	}

	private function applyHide(PlayerChatEvent $event, string $message, array $words, Player $player) : void {
		if ((bool) $this->plugin->getConfig()->get("removeUnicode")) {
			$event->setMessage(PluginAPI::removeUnicode(PluginAPI::removeProfanity($message, $words, ($this->plugin->getConfig()->get("replacementCharacter") ?? "#"))), (int) ($this->plugin->getConfig()->get("remove-unicode") ?? 1), (bool) ($this->plugin->getConfig()->get("mb-strlen") ?? false));
		} else {
			$event->setMessage(PluginAPI::removeProfanity($message, $words));
		}
		$this->plugin->getLogger()->warning($this->renderMessage("hide-warning-message", $player, $player->getName()));
	}

	private function handlePunishment(Player $player) : void {
		$playerName = $player->getName();
		if (($this->plugin->punishment[$playerName] ?? 0) === $this->plugin->getConfig()->get("max-violations")) {
			$punishType = $this->plugin->getConfig()->get("punishment-type");
			switch ($punishType) {
				case "ban":
					$this->plugin->punishment[$playerName] = isset($this->plugin->punishment[$playerName]);
					$player->getServer()->getNameBans()->addBan($playerName, "Profanity", $this->duration[0], $player->getServer()->getName());
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ned",
					]));
					$this->plugin->getLogger()->warning($this->renderMessage("ban-warning-message", $player, $playerName));
					break;
				case "kick":
					$this->plugin->punishment[$playerName] = isset($this->plugin->punishment[$playerName]);
					$player->kick($this->renderMessage("kick-message", $player, $playerName, [
						"type" => $punishType . "ed",
					]));
					$this->plugin->getLogger()->warning($this->renderMessage("kick-warning-message", $player, $playerName));
					break;
				case "command":
					$this->plugin->punishment[$playerName] = isset($this->plugin->punishment[$playerName]);
					$this->plugin->getLogger()->warning($this->renderMessage("command-warning-message", $player, $playerName));
					if ((bool) $this->plugin->getConfig()->get("execute-as-player")) {
						$this->plugin->getServer()->dispatchCommand($player, $this->renderMessage("command", $player, $playerName));
					} else {
						$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()), $this->renderMessage("command", $player, $playerName));
					}
					break;
				default:
					throw new Exception("Cannot Identify the type of punishment in config.yml!");
			}
			return;
		}

		$this->plugin->punishment[$playerName] = isset($this->plugin->punishment[$playerName]) ? $this->plugin->punishment[$playerName] + 1 : 1;
	}

	private function renderMessage(string $configKey, ?\pocketmine\player\Player $player = null, ?string $playerName = null, array $extra = []) : string {
		return InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get($configKey)), [
			"player" => $player,
			"player_name" => $playerName ?? ($player?->getName() ?? ""),
			...$extra,
		], $player);
	}
}
