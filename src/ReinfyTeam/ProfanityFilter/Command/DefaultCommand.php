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

namespace ReinfyTeam\ProfanityFilter\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as T;
use pocketmine\utils\TextFormat as TF;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\Forms\CustomForm;
use ReinfyTeam\ProfanityFilter\Utils\Forms\SimpleForm;
use ReinfyTeam\ProfanityFilter\Utils\Language;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;

class DefaultCommand extends Command implements PluginOwned {
	private Loader $plugin;

	private Language $language;

	private const CMD_USAGE = "profanity-command-usage-execute";

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
		if (!$this->testPermission($sender)) {
			return;
		}

		if (!isset($args[0])) {
			$this->sendLang($sender, self::CMD_USAGE);
			return;
		}

		switch ($args[0]) {
			case "help":
				$this->handleHelp($sender);
				break;
			case "ui":
			case "gui":
			case "form":
				$this->handleForm($sender);
				break;
			case "info":
			case "credits":
				$this->handleInfo($sender);
				break;
			case "list":
			case "words":
			case "banned-words":
				$this->handleList($sender);
				break;
			case "toggle":
				$this->handleToggle($sender);
				break;
			case "remove":
				$this->handleRemove($sender, $args);
				break;
			case "add":
				$this->handleAdd($sender, $args);
				break;
			case "reload":
				$this->handleReload($sender);
				break;
			default:
				$this->sendLang($sender, self::CMD_USAGE);
				break;
		}
	}

	private function handleHelp(CommandSender $sender) : void {
		$this->sendLang($sender, "help-title");
		$this->sendLang($sender, "help-subtitle");
		foreach ($this->language->getLanguage()->get("help-page") as $command) {
			$sender->sendMessage(PluginUtils::colorize("- " . $command));
		}
	}

	private function handleForm(CommandSender $sender) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!$sender instanceof Player) {
			$this->sendLang($sender, "profanity-command-only-ingame");
			return;
		}

		$this->sendForm($sender);
	}

	private function handleInfo(CommandSender $sender) : void {
		$this->sendLang($sender, "credits-title");
		$this->sendLang($sender, "credits-subtitle");
		$this->sendLang($sender, "credits-description");
		foreach ($this->plugin->getDescription()->getAuthors() as $author) {
			$sender->sendMessage("- " . T::GREEN . $author);
		}
	}

	private function handleList(CommandSender $sender) : void {
		$this->sendLang($sender, "banned-words-description-1");
		foreach ($this->plugin->getProfanity()->get("banned-words") as $word) {
			$sender->sendMessage("- " . $word);
		}
		$this->sendLang($sender, "banned-words-description-2");
	}

	private function handleToggle(CommandSender $sender) : void {
		if (Loader::$enabled) {
			$this->sendLang($sender, "ui-pf-manage-disabled-profanityfilter");
			Loader::$enabled = false;
			return;
		}

		Loader::$enabled = true;
		$this->sendLang($sender, "ui-pf-manage-enabled-profanityfilter");
	}

	private function handleRemove(CommandSender $sender, array $args) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!$this->hasArg($args, 1)) {
			$this->sendLang($sender, self::CMD_USAGE);
			return;
		}

		PluginUtils::removeProfanityWord($args[1]);
		$this->sendLang($sender, "profanity-command-removed-word");
	}

	private function handleAdd(CommandSender $sender, array $args) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!$this->hasArg($args, 1)) {
			$this->sendLang($sender, self::CMD_USAGE);
			return;
		}

		PluginUtils::addProfanityWord($args[1]);
		$this->sendLang($sender, "profanity-command-added-word");
	}

	private function handleReload(CommandSender $sender) : void {
		$this->plugin->getProfanity(true);
		$this->plugin->getConfig()->reload();
		$this->sendLang($sender, "profanity-command-reload-complete");
	}

	private function hasArg(array $args, int $index) : bool {
		return isset($args[$index]) && $args[$index] !== "";
	}

	private function sendLang(CommandSender $sender, string $key) : void {
		$sender->sendMessage($this->language->translateMessage($key));
	}

	private function shouldRejectCustom() : bool {
		return !Loader::getInstance()->getConfig()->get("profanity") === "custom";
	}

	/**
	 * Profanity Form Interface.
	 */
	private function sendForm(Player $player, bool $added = false) {
		$form = new SimpleForm(function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			switch ($data) {
				case 0:
					$this->viewList($player);
					break;
				case 1:
					if (Loader::$enabled) {
						$player->sendMessage($this->language->translateMessage("ui-pf-manage-disabled-profanityfilter"));
						Loader::$enabled = false;
					} else {
						Loader::$enabled = true;
						$player->sendMessage($this->language->translateMessage("ui-pf-manage-enabled-profanityfilter"));
					}
					break;
				case 2:
					$this->addProfanityWordForm($player);
					break;
				case 3:
					$this->plugin->getProfanity(true);
					$this->plugin->getConfig()->reload();
					$player->sendMessage($this->language->translateMessage("profanity-ui-reload-complete"));
					break;
				default:
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		if ($added) {
			$form->setContent($this->language->translateMessage("ui-pf-manage-added-done"));
		} else {
			$form->setContent($this->language->translateMessage("ui-pf-manage-description"));
		}
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-1"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-2"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-3"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-4"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-exit"));
		$player->sendForm($form);
	}

	/**
	 * Profanity Form Interface.
	 */
	private function viewList(Player $player, bool $removed = false) {
		$form = new SimpleForm(function (Player $player, $data) {
			if ($data === null) {
				$this->sendForm($player);
				return;
			}

			switch ($data) {
				case "return":
					$this->sendForm($player);
					break;
				default:
					$this->viewActions($player, $data);
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		if ($removed) {
			$form->setContent($this->language->translateMessage("ui-pf-manage-remove-done"));
		}
		foreach ($this->plugin->getProfanity()->get("banned-words") as $word) {
			$form->addButton(T::DARK_RED . $word, 0, "", $word);
		}
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"), -1, "", "return");
		$player->sendForm($form);
	}

	public function viewActions(Player $player, $word) : void {
		$form = new SimpleForm(function (Player $player, $data) use ($word) {
			if ($data === null) {
				$this->viewList($player);
				return;
			}

			switch ($data) {
				case 0:
					PluginUtils::removeProfanityWord($word);
					$this->viewList($player, true);
					break;
				case 1:
					$this->viewList($player);
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		$form->setContent(TF::RED . "Manage: " . $word);
		$form->addButton($this->language->translateMessage("ui-pf-manage-actions-button-remove"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"));
		$player->sendForm($form);
	}

	public function addProfanityWordForm(Player $player, bool $nodata = false) : void {
		$form = new CustomForm(function (Player $player, $data) use ($nodata) {
			if ($data === null) {
				$this->viewList($player);
				return;
			}

			if ($data[1] === "") {
				$this->addProfanityWordForm($player, true);
				return;
			}

			PluginUtils::addProfanityWord($data[1]);
			$this->sendForm($player, true);
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		if ($nodata) {
			$form->addLabel($this->language->translateMessage("ui-pf-addform-specify"));
		} else {
			$form->addLabel($this->language->translateMessage("ui-pf-addform-description"));
		}
		$form->addInput("", $this->language->translateMessage("ui-pf-addform-example"));
		$player->sendForm($form);
	}
}
