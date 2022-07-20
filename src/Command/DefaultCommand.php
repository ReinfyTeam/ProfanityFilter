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

namespace ReinfyTeam\ProfanityFilter\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as T;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\Forms\SimpleForm;
use ReinfyTeam\ProfanityFilter\Utils\Language;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;

class DefaultCommand extends Command implements PluginOwned {
	private Loader $plugin;

	private Language $language;

	public function getOwningPlugin() : Loader {
		return $this->plugin;
	}

	public function __construct() {
		$this->plugin = Loader::getInstance();
		$this->language = new Language();
		parent::__construct("profanityfilter", "ProfanityFilter Management", "/profanityfilter <help/subcommand>", ["pf"]);
		$this->setPermission(($this->plugin->getConfig()->get("command-permission") ?? "profanityfilter.command"));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if ($sender instanceof Player) {
			if (!$this->testPermission($sender)) {
				return;
			}
			if (!isset($args[0])) {
				$sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
				return;
			}

			switch ($args[0]) {
				case "help":
					$sender->sendMessage($this->language->translateMessage("help-title"));
					$sender->sendMessage($this->language->translateMessage("help-subtitle"));
					foreach ($this->language->getLanguage()->get("help-page") as $command) {
						$sender->sendMessage(PluginUtils::colorize("- " . $command));
					}
					break;
				case "ui":
				case "gui":
				case "form":
					if (!$sender instanceof Player) {
						$sender->sendMessage($this->language->translateMessage("profanity-command-only-ingame"));
					} else {
						$this->sendForm($sender);
					}
					break;
				case "info":
				case "credits":
					$sender->sendMessage($this->language->translateMessage("credits-title"));
					$sender->sendMessage($this->language->translateMessage("credits-subtitle"));
					$sender->sendMessage($this->language->translateMessage("credits-description"));
					foreach ($this->plugin->getDescription()->getAuthors() as $author) {
						$sender->sendMessage("- " . T::GREEN . $author);
					}
					break;
				case "list":
				case "words":
				case "banned-words":
					$sender->sendMessage($this->language->translateMessage("banned-words-description"));
					foreach ($this->plugin->getProfanity()->get("banned-words") as $word) {
						$sender->sendMessage("- " . $word);
					}
					$sender->sendMessage($this->language->translateMessage("banned-words-description-2"));
					break;
				default:
					$sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
					break;
			}
		} else {
			if (!isset($args[0])) {
				$sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
				return;
			}

			switch ($args[0]) {
				case "help":
					$sender->sendMessage($this->language->translateMessage("help-title"));
					$sender->sendMessage($this->language->translateMessage("help-subtitle"));
					foreach ($this->language->getLanguage()->get("help-page") as $command) {
						$sender->sendMessage(PluginUtils::colorize("- " . $command));
					}
					break;
				case "ui":
				case "gui":
				case "form":
					$sender->sendMessage($this->language->translateMessage("profanity-command-only-ingame"));
					break;
				case "info":
				case "credits":
					$sender->sendMessage($this->language->translateMessage("credits-title"));
					$sender->sendMessage($this->language->translateMessage("credits-subtitle"));
					$sender->sendMessage($this->language->translateMessage("credits-description"));
					foreach ($this->plugin->getDescription()->getAuthors() as $author) {
						$sender->sendMessage("- " . T::GREEN . $author);
					}
					break;
				case "list":
				case "words":
				case "banned-words":
					$sender->sendMessage($this->language->translateMessage("banned-words-description"));
					foreach ($this->plugin->getProfanity()->get("banned-words") as $word) {
						$sender->sendMessage("- " . $word);
					}
					$sender->sendMessage($this->language->translateMessage("banned-words-description-2"));
					break;
				default:
					$sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
					break;
			}
		}
	}

	/**
	 * Profanity Form Interface.
	 */
	private function sendForm(Player $player) {
		$form = new SimpleForm(function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			switch ($data) {
				case 0:
					$this->viewList($player);
					break;
				case 1:
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		$form->setContent($this->language->translateMessage("ui-pf-manage-description"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-1"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-exit"));
		$player->sendForm($form);
	}

	/**
	 * Profanity Form Interface.
	 */
	private function viewList(Player $player) {
		$form = new SimpleForm(function (Player $player, $data) {
			if ($data === null) {
				$this->sendForm($player);
				return;
			}

			switch ($data) {
				case 0:
					$this->sendForm($player);
					break;
				default:
					$this->viewList($player);
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"));
		foreach ($this->plugin->getProfanity()->get("banned-words") as $word) {
			$form->addButton(T::RED . $word);
		}
		$player->sendForm($form);
	}
}
