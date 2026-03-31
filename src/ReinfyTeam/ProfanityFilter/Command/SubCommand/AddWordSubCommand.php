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

use ReinfyTeam\ProfanityFilter\libs\_b8923f35e54afff0\CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use function is_string;

class AddWordSubCommand extends BaseProfanitySubCommand {
	private const ARG_WORD = "word";

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument(self::ARG_WORD, false));
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if ($this->shouldRejectCustom()) {
			$this->sendLang($sender, "profanity-command-use-custom-pf-instead");
			return;
		}

		if (!isset($args[self::ARG_WORD]) || $args[self::ARG_WORD] === "") {
			$this->sendUsage();
			return;
		}

		if (!is_string($args[self::ARG_WORD])) {
			$this->sendUsage();
			return;
		}

		if (!PluginUtils::addProfanityWord($args[self::ARG_WORD])) {
			$this->sendLang($sender, "profanity-command-add-blocked");
			return;
		}

		$this->sendLang($sender, "profanity-command-added-word");
	}
}