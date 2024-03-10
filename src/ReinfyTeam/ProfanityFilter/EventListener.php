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
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use ReinfyTeam\ProfanityFilter\libs\_e1bc4338b0a70208\SOFe\InfoAPI\InfoAPI;

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

		if (!Loader::$enabled) {
			return;
		}

		if (strtolower($this->provider) === "custom") {
			$words = Loader::getInstance()->getProfanity()->get("banned-words");
		} else {
			$words = (array) ($this->plugin->getProvidedProfanities() ?? PluginAPI::defaultProfanity());
		}
		if ($player->hasPermission(($this->plugin->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"))) {
			return;
		}
		if (PluginAPI::detectProfanity($message, $words)) {
			switch ($this->type) {
				case "block":
					$event->cancel();
					$player->sendMessage(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("block-message")), [], $player));
					$this->plugin->getLogger()->warning(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("block-warning-message")), [
						"player" => $player,
						"player_name" => $player->getName(), // backwards compatibility
					]));
					break;
				case "hide":
					/**
					 * Detect if theres unicode inside of profanity. It will removed if config was set to true...
					 * TODO: Improve this unicode blocking
					 */
					if ((bool) $this->plugin->getConfig()->get("removeUnicode")) {
						$event->setMessage(PluginAPI::removeUnicode(PluginAPI::removeProfanity($message, $words, ($this->plugin->getConfig()->get("replacementCharacter") ?? "#"))), (int) ($this->plugin->getConfig()->get("remove-unicode") ?? 1), (bool) ($this->plugin->getConfig()->get("mb-strlen") ?? false));
					} else {
						$event->setMessage(PluginAPI::removeProfanity($message, $words));
					}
					$this->plugin->getLogger()->warning(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("hide-warning-message")), [
						"player" => $player,
						"player_name" => $player->getName(), // backwards compatibility
					]));
					break;
				default:
					throw new Exception("Cannot Identify the type of profanity in config.yml");
			}
			if (($this->plugin->punishment[$player->getName()] ?? 0) === $this->plugin->getConfig()->get("max-violations")) {
				$punishType = $this->plugin->getConfig()->get("punishment-type");
				switch ($punishType) {
					case "ban":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->getServer()->getNameBans()->addBan($player->getName(), "Profanity", $this->duration[0], $player->getServer()->getName());
						$player->kick(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("kick-message")), [
							"player" => $player,
							"type" => $punishType . "ned",
						], $player));
						$this->plugin->getLogger()->warning(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("ban-warning-message")), [
							"player" => $player,
							"player_name" => $player->getName(), // backwards compatibility
						]));
						break;
					case "kick":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->kick(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("kick-message")), [
							"player" => $player,
							"type" => $punishType . "ed", //why??
						], $player));
						$this->plugin->getLogger()->warning(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("kick-warning-message")), [
							"player" => $player,
							"player_name" => $player->getName(), // backwards compatibility
						]));
						break;
					case "command":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$this->plugin->getLogger()->warning(InfoAPI::render($this->plugin, PluginUtils::colorize($this->plugin->getConfig()->get("command-warning-message")), [
							"player" => $player,
							"player_name" => $player->getName(), // backwards compatibility
						]));
						if ((bool) $this->plugin->getConfig()->get("execute-as-player")) {
							$this->plugin->getServer()->dispatchCommand($player, InfoAPI::render($this->plugin, $this->plugin->getConfig()->get("command"), [
								"player" => $player,
								"player_name" => $player->getName(), // backwards compatibility
							]));
						} else {
							$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()), InfoAPI::render($this->plugin, $this->plugin->getConfig()->get("command"), [
								"player" => $player,
								"player_name" => $player->getName(), // backwards compatibility
							]));
						}
						break;
					default:
						throw new Exception("Cannot Identify the type of punishment in config.yml!");
				}
			} else {
				$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ? $this->plugin->punishment[$player->getName()] + 1 : 1;
			}
		}
	}
}