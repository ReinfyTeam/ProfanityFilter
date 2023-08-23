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

namespace ReinfyTeam\ProfanityFilter\Utils;

use pocketmine\utils\Config;
use ReinfyTeam\ProfanityFilter\Loader;
use function file_exists;

class Language {
	private Loader $plugin;

	public function __construct() {
		$this->plugin = Loader::getInstance();
	}

	public function getLanguage() : Config {
		return new Config($this->plugin->getDataFolder() . "languages/" . $this->getSelectedLanguage() . ".yml");
	}

	public function getSelectedLanguage() : string {
		return $this->plugin->getConfig()->get("lang");
	}

	/**
	 * Translate Message from Language Configuration
	 * Do not call it directly.
	 */
	public function translateMessage(mixed $option) : mixed {
		$lang = $this->getLanguage();

		/** Check if selected language is missing. **/
		if (!file_exists($this->plugin->getDataFolder() . "languages/" . $this->getSelectedLanguage() . ".yml")) {
			throw new \Exception("Missing file in " . $this->plugin->getDataFolder() . "languages/" . $this->getSelectedLanguage() . ".yml");
		}

		/** Check if option is exist. **/
		if ($lang->get($option) === null) {
			throw new \Exception("Trying to access on null.");
		}

		return PluginUtils::colorize($lang->get($option));
	}

	public function init() : void {
		if (!file_exists($this->plugin->getDataFolder() . "language/" . $this->getSelectedLanguage() . ".yml")) {
			$this->plugin->saveResource("languages/" . $this->getSelectedLanguage() . ".yml");
		}
	}
}
