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

namespace xqwtxon\ProfanityFilter;

use Exception;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use xqwtxon\ProfanityFilter\Utils\PluginUtils;

class EventListener implements Listener {
	private Loader $plugin;

	private string $type;

	private ?array $duration;

	public function __construct(string $type) {
		$this->plugin = Loader::getInstance();
		$this->type = $type;
		$this->duration = Loader::getInstance()->getDuration();
	}

	/**
	 * When player chat.
	 */
	public function onChat(PlayerChatEvent $event) : void {
		$message = $event->getMessage();
		$player = $event->getPlayer();
		$words = $this->plugin->getProfanity()->get("banned-words");
		if ($player->hasPermission(($this->plugin->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"))) {
			return;
		}
		if (PluginAPI::detectProfanity($message, $words)) {
			switch ($this->type) {
				case "block":
					$event->cancel();
					$player->sendMessage(PluginUtils::colorize($this->plugin->getConfig()->get("block-message")));
					break;
				case "hide":
					/**
					 * Detect if theres unicode inside of profanity. It will removed if config was set to true...
					 * TODO: Improve this unicode blocking
					 */
					if ((bool) $this->plugin->getConfig()->get("removeUnicode")) {
						$event->setMessage(PluginAPI::removeUnicode(PluginAPI::removeProfanity($message, $words, ($this->plugin->getConfig()->get("replacementCharacter") ?? "#"))));
					} else {
						$event->setMessage(PluginAPI::removeProfanity($message, $words));
					}
					break;
				default:
					throw new Exception("Cannot Identify the type of profanity in config.yml");
			}
			if (($this->plugin->punishment[$player->getName()] ?? 0) === $this->plugin->getConfig()->get("max-violations")) {
				switch ($this->plugin->getConfig()->get("punishment-type")) {
					case "ban":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->getServer()->getNameBans()->addBan($player->getName(), "Profanity", $this->duration[0], $player->getServer()->getName());
						$player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
						break;
					case "kick":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
						break;
					default:
						throw new Exception("Cannot Identify the type of punishment in config.yml");
				}
			} else {
				$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ? $this->plugin->punishment[$player->getName()] + 1 : 1;
			}
		}
	}

	/**
	 * When player chats command.
	 */
	public function onCommand(PlayerCommandPreprocessEvent $event) : void {
		$message = $event->getMessage();
		$player = $event->getPlayer();
		$words = $this->plugin->getProfanity()->get("banned-words");
		if ($player->hasPermission(($this->plugin->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"))) {
			return;
		}
		if (PluginAPI::detectProfanity($message, $words)) {
			switch ($this->type) {
				case "block":
					$event->cancel();
					$player->sendMessage(PluginUtils::colorize($this->plugin->getConfig()->get("block-message")));
					break;
				case "hide":
					$event->setMessage(PluginAPI::removeProfanity($message, $words));
					break;
				default:
					throw new Exception("Cannot Identify the type of profanity in config.yml");
			}

			if (($this->plugin->punishment[$player->getName()] ?? 0) === $this->plugin->getConfig()->get("max-violations")) {
				switch ($this->plugin->getConfig()->get("punishment-type")) {
					case "ban":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->getServer()->getNameBans()->addBan($player->getName(), "Profanity", $this->duration[0], $player->getServer()->getName());
						$player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
						break;
					case "kick":
						$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]);
						$player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
						break;
					default:
						throw new Exception("Cannot Identify the type of punishment in config.yml");
				}
			} else {
				$this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ? $this->plugin->punishment[$player->getName()] + 1 : 1;
			}
		}
	}
}
