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
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\Forms\CustomForm;
use ReinfyTeam\ProfanityFilter\Utils\Forms\SimpleForm;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;

class ProfanityFilterCommand extends Command implements PluginOwned {
	private const BUTTON_VIEW_LIST = 0;
	private const BUTTON_TOGGLE = 1;
	private const BUTTON_ADD_WORD = 2;
	private const BUTTON_RELOAD = 3;
	private const BUTTON_RETURN_LABEL = "return";
	private const ACTION_REMOVE_WORD = 0;
	private const WORD_ARGUMENT_INDEX = 1;
	private const WORD_FORM_INPUT_INDEX = 1;

	private Loader $plugin;

	private LanguageManager $language;

	private const CMD_USAGE = "profanity-command-usage-execute";

	public function getOwningPlugin() : Loader {
		return $this->plugin;
	}

	public function __construct() {
		$this->plugin = Loader::getInstance();
		$this->language = new LanguageManager();
		parent::__construct("profanityfilter", "ProfanityFilter Management", "/profanityfilter <help/subcommand>", ["pf"]);
		$permission = $this->plugin->getConfig()->get("command-permission");
		$this->setPermission(is_string($permission) ? $permission : null);
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
		/** @var string[] $help */
		$help = (array) $this->language->getLanguageConfig()->get("help-page");
		foreach ($help as $command) {
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
		/** @var string[] $words */
		$words = (array) $this->plugin->getProfanityConfig()->get("banned-words");
		foreach ($words as $word) {
			$sender->sendMessage("- " . $word);
		}
		$this->sendLang($sender, "banned-words-description-2");
	}

	private function handleToggle(CommandSender $sender) : void {
		if (Loader::$isFilterEnabled) {
			$this->sendLang($sender, "ui-pf-manage-disabled-profanityfilter");
			Loader::$isFilterEnabled = false;
			return;
		}

		Loader::$isFilterEnabled = true;
		$this->sendLang($sender, "ui-pf-manage-enabled-profanityfilter");
	}

	/**
	 * @param array<int, string> $args
	 */
	private function handleRemove(CommandSender $sender, array $args) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!$this->hasArg($args, self::WORD_ARGUMENT_INDEX)) {
			$this->sendLang($sender, self::CMD_USAGE);
			return;
		}

		PluginUtils::removeProfanityWord($args[self::WORD_ARGUMENT_INDEX]);
		$this->sendLang($sender, "profanity-command-removed-word");
	}

	/**
	 * @param array<int, string> $args
	 */
	private function handleAdd(CommandSender $sender, array $args) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!$this->hasArg($args, self::WORD_ARGUMENT_INDEX)) {
			$this->sendLang($sender, self::CMD_USAGE);
			return;
		}

		PluginUtils::addProfanityWord($args[self::WORD_ARGUMENT_INDEX]);
		$this->sendLang($sender, "profanity-command-added-word");
	}

	private function handleReload(CommandSender $sender) : void {
		$this->plugin->getProfanityConfig(true);
		$this->plugin->getConfig()->reload();
		$this->sendLang($sender, "profanity-command-reload-complete");
	}

	/**
	 * @param array<int, string> $args
	 */
	private function hasArg(array $args, int $index) : bool {
		return isset($args[$index]) && $args[$index] !== "";
	}

	private function sendLang(CommandSender $sender, string $key) : void {
		$sender->sendMessage($this->language->translateMessage($key));
	}

	private function shouldRejectCustom() : bool {
		$provider = Loader::getInstance()->getConfig()->get("profanity");
		return strtolower(is_string($provider) ? $provider : "") !== Loader::PROVIDER_CUSTOM;
	}

	/**
	 * Profanity Form Interface.
	 */
	private function sendForm(Player $player, bool $added = false) : void {
		$form = new SimpleForm(function (Player $player, mixed $data) {
			if ($data === null) {
				return;
			}

			switch ($data) {
				case self::BUTTON_VIEW_LIST:
					$this->viewList($player);
					break;
				case self::BUTTON_TOGGLE:
					if (Loader::$isFilterEnabled) {
						$player->sendMessage($this->language->translateMessage("ui-pf-manage-disabled-profanityfilter"));
						Loader::$isFilterEnabled = false;
					} else {
						Loader::$isFilterEnabled = true;
						$player->sendMessage($this->language->translateMessage("ui-pf-manage-enabled-profanityfilter"));
					}
					break;
				case self::BUTTON_ADD_WORD:
					$this->addProfanityWordForm($player);
					break;
				case self::BUTTON_RELOAD:
					$this->plugin->getProfanityConfig(true);
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
	private function viewList(Player $player, bool $removed = false) : void {
		$form = new SimpleForm(function (Player $player, mixed $data) {
			if ($data === null) {
				$this->sendForm($player);
				return;
			}

			switch ($data) {
				case self::BUTTON_RETURN_LABEL:
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
		/** @var string[] $words */
		$words = (array) $this->plugin->getProfanityConfig()->get("banned-words");
		foreach ($words as $word) {
			if (!is_string($word)) {
				continue;
			}
			$form->addButton(T::DARK_RED . $word, SimpleForm::IMAGE_TYPE_PATH, "", $word);
		}
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"), SimpleForm::IMAGE_TYPE_NONE, "", self::BUTTON_RETURN_LABEL);
		$player->sendForm($form);
	}

	public function viewActions(Player $player, string $word) : void {
		$form = new SimpleForm(function (Player $player, mixed $data) use ($word) {
			if ($data === null) {
				$this->viewList($player);
				return;
			}

			switch ($data) {
				case self::ACTION_REMOVE_WORD:
					PluginUtils::removeProfanityWord($word);
					$this->viewList($player, true);
					break;
				case self::BUTTON_VIEW_LIST:
					$this->viewList($player);
					break;
			}
		});

		$form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
		$form->setContent(T::RED . "Manage: " . $word);
		$form->addButton($this->language->translateMessage("ui-pf-manage-actions-button-remove"));
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"));
		$player->sendForm($form);
	}

	public function addProfanityWordForm(Player $player, bool $nodata = false) : void {
		$form = new CustomForm(function (Player $player, ?array $data) {
			if ($data === null) {
				$this->viewList($player);
				return;
			}

			if ($data[self::WORD_FORM_INPUT_INDEX] === "") {
				$this->addProfanityWordForm($player, true);
				return;
			}

			PluginUtils::addProfanityWord($data[self::WORD_FORM_INPUT_INDEX]);
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



