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

use ReinfyTeam\ProfanityFilter\libs\_ed051197693bb105\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use function is_string;
use function strtolower;

abstract class BaseProfanitySubCommand extends BaseSubCommand {
	protected Loader $loader;

	protected LanguageManager $language;

	public function __construct(Loader $plugin, string $name, string $description = "", array $aliases = []) {
		$this->loader = $plugin;
		$this->language = new LanguageManager();
		parent::__construct($plugin, $name, $description, $aliases);
	}

	protected function getLoader() : Loader {
		return $this->loader;
	}

	protected function sendLang(CommandSender $sender, string $key) : void {
		$sender->sendMessage($this->language->translateMessage($key));
	}

	protected function shouldRejectCustom() : bool {
		$provider = $this->loader->getConfig()->get("profanity");
		return strtolower(is_string($provider) ? $provider : "") !== Loader::PROVIDER_CUSTOM;
	}
}