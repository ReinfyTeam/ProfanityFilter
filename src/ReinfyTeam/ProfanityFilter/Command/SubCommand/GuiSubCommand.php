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

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as T;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\Forms\CustomForm;
use ReinfyTeam\ProfanityFilter\Utils\Forms\SimpleForm;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use function is_string;

class GuiSubCommand extends BaseProfanitySubCommand {
	private const BUTTON_VIEW_LIST = 0;
	private const BUTTON_TOGGLE = 1;
	private const BUTTON_ADD_WORD = 2;
	private const BUTTON_RELOAD = 3;
	private const BUTTON_RETURN_LABEL = "return";
	private const ACTION_REMOVE_WORD = 0;
	private const WORD_FORM_INPUT_INDEX = 1;

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
					$this->getLoader()->getProfanityConfig(true);
					$this->getLoader()->getConfig()->reload();
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
		$words = (array) $this->getLoader()->getProfanityConfig()->get("banned-words");
		foreach ($words as $word) {
			if (!is_string($word)) {
				continue;
			}
			$form->addButton(T::DARK_RED . $word, SimpleForm::IMAGE_TYPE_PATH, "", $word);
		}
		$form->addButton($this->language->translateMessage("ui-pf-manage-button-return"), SimpleForm::IMAGE_TYPE_NONE, "", self::BUTTON_RETURN_LABEL);
		$player->sendForm($form);
	}

	private function viewActions(Player $player, string $word) : void {
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

	private function addProfanityWordForm(Player $player, bool $nodata = false) : void {
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
