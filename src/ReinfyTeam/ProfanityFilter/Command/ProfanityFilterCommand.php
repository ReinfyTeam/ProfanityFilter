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

use ReinfyTeam\ProfanityFilter\libs\_ed051197693bb105\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\AddWordSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\GuiSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\HelpSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\InfoSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\ListSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\ReloadSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\RemoveWordSubCommand;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\ToggleSubCommand;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use function is_string;

class ProfanityFilterCommand extends BaseCommand implements PluginOwned {
	private Loader $loader;

	private LanguageManager $language;

	private const CMD_USAGE = "profanity-command-usage-execute";

	public function __construct(Loader $plugin) {
		$this->loader = $plugin;
		$this->language = new LanguageManager();
		parent::__construct($plugin, "profanityfilter", "ProfanityFilter Management", ["pf"]);
	}

	protected function prepare() : void {
		$permission = $this->loader->getConfig()->get("command-permission");
		$this->setPermission(is_string($permission) ? $permission : null);

		$this->registerSubCommand(new HelpSubCommand($this->loader, "help"));
		$this->registerSubCommand(new GuiSubCommand($this->loader, "ui", "Open the management UI", ["gui", "form"]));
		$this->registerSubCommand(new InfoSubCommand($this->loader, "info", "Credits", ["credits"]));
		$this->registerSubCommand(new ListSubCommand($this->loader, "list", "List banned words", ["words", "banned-words"]));
		$this->registerSubCommand(new ToggleSubCommand($this->loader, "toggle"));
		$this->registerSubCommand(new RemoveWordSubCommand($this->loader, "remove"));
		$this->registerSubCommand(new AddWordSubCommand($this->loader, "add"));
		$this->registerSubCommand(new ReloadSubCommand($this->loader, "reload"));
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage($this->language->translateMessage(self::CMD_USAGE));
	}

	public function getOwningPlugin() : Loader {
		return $this->loader;
	}
}