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
use ReinfyTeam\ProfanityFilter\Loader;

class ToggleSubCommand extends BaseProfanitySubCommand {
	protected function prepare() : void {
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if (Loader::$isFilterEnabled) {
			Loader::$isFilterEnabled = false;
			$this->sendLang($sender, "ui-pf-manage-disabled-profanityfilter");
			return;
		}

		Loader::$isFilterEnabled = true;
		$this->sendLang($sender, "ui-pf-manage-enabled-profanityfilter");
	}
}
