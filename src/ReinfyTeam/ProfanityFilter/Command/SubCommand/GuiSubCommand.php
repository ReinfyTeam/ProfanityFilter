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

namespace ReinfyTeam\ProfanityFilter\Command\SubCommand;

use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\CustomForm as PmCustomForm;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\element\Input;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\element\Label;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\MenuForm;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\MenuOption;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as T;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use function count;
use function is_string;
use function trim;

class GuiSubCommand extends BaseProfanitySubCommand {
	private const BUTTON_VIEW_LIST = 0;
	private const BUTTON_TOGGLE = 1;
	private const BUTTON_ADD_WORD = 2;
	private const BUTTON_RELOAD = 3;
	private const ACTION_REMOVE_WORD = 0;

	protected function prepare() : void {
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
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

	/**
	 * Profanity Form Interface.
	 */
	private function sendForm(Player $player, bool $added = false) : void {
		$title = $this->language->translateMessage("ui-pf-manage-title");
		$content = $added ? $this->language->translateMessage("ui-pf-manage-added-done") : $this->language->translateMessage("ui-pf-manage-description");
		$options = [
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-1")),
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-2")),
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-3")),
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-4")),
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-exit")),
		];

		$form = new MenuForm(
			$title,
			$content,
			$options,
			function (Player $player, int $selected) : void {
				switch ($selected) {
					case self::BUTTON_VIEW_LIST:
						$this->viewList($player);
						return;
					case self::BUTTON_TOGGLE:
						if (Loader::$isFilterEnabled) {
							$player->sendMessage($this->language->translateMessage("ui-pf-manage-disabled-profanityfilter"));
							Loader::$isFilterEnabled = false;
						} else {
							Loader::$isFilterEnabled = true;
							$player->sendMessage($this->language->translateMessage("ui-pf-manage-enabled-profanityfilter"));
						}
						return;
					case self::BUTTON_ADD_WORD:
						$this->addProfanityWordForm($player);
						return;
					case self::BUTTON_RELOAD:
						$this->getLoader()->getProfanityConfig(true);
						$this->getLoader()->getConfig()->reload();
						$player->sendMessage($this->language->translateMessage("profanity-ui-reload-complete"));
						return;
					default:
						return;
				}
			}
		);

		$player->sendForm($form);
	}

	/**
	 * Profanity Form Interface.
	 */
	private function viewList(Player $player, bool $removed = false) : void {
		$title = $this->language->translateMessage("ui-pf-manage-title");
		$content = $removed ? $this->language->translateMessage("ui-pf-manage-remove-done") : $this->language->translateMessage("ui-pf-manage-description");
		$words = [];
		/** @var string[] $raw */
		$raw = (array) $this->getLoader()->getProfanityConfig()->get("banned-words");
		foreach ($raw as $word) {
			if (is_string($word)) {
				$words[] = $word;
			}
		}

		$options = [];
		foreach ($words as $word) {
			$options[] = new MenuOption(T::DARK_RED . $word);
		}
		$options[] = new MenuOption($this->language->translateMessage("ui-pf-manage-button-return"));

		$form = new MenuForm(
			$title,
			$content,
			$options,
			function (Player $player, int $selected) use ($words) : void {
				if ($selected === count($words)) {
					$this->sendForm($player);
					return;
				}
				$word = $words[$selected] ?? null;
				if ($word === null) {
					$this->sendForm($player);
					return;
				}
				$this->viewActions($player, $word);
			},
			function (Player $player) : void {
				$this->sendForm($player);
			}
		);

		$player->sendForm($form);
	}

	private function viewActions(Player $player, string $word) : void {
		$options = [
			new MenuOption($this->language->translateMessage("ui-pf-manage-actions-button-remove")),
			new MenuOption($this->language->translateMessage("ui-pf-manage-button-return")),
		];
		$form = new MenuForm(
			$this->language->translateMessage("ui-pf-manage-title"),
			T::RED . "Manage: " . $word,
			$options,
			function (Player $player, int $selected) use ($word) : void {
				if ($selected === self::ACTION_REMOVE_WORD) {
					PluginUtils::removeProfanityWord($word);
					$this->viewList($player, true);
					return;
				}
				$this->viewList($player);
			},
			function (Player $player) : void {
				$this->viewList($player);
			}
		);

		$player->sendForm($form);
	}

	private function addProfanityWordForm(Player $player, ?string $infoKey = null) : void {
		$title = $this->language->translateMessage("ui-pf-manage-title");
		$elements = [
			new Label("info", $this->language->translateMessage($infoKey ?? "ui-pf-addform-description")),
			new Input("word", "", $this->language->translateMessage("ui-pf-addform-example")),
		];

		$form = new PmCustomForm(
			$title,
			$elements,
			function (Player $player, \ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\dktapps\pmforms\CustomFormResponse $response) : void {
				$word = trim($response->getString("word"));
				if ($word === "") {
					$this->addProfanityWordForm($player, "ui-pf-addform-specify");
					return;
				}
				if (!PluginUtils::addProfanityWord($word)) {
					$this->addProfanityWordForm($player, "ui-pf-addform-blocked");
					return;
				}
				$this->sendForm($player, true);
			},
			function (Player $player) : void {
				$this->viewList($player);
			}
		);

		$player->sendForm($form);
	}
}