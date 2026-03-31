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

namespace ReinfyTeam\ProfanityFilter\Utils\Forms;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm {
	protected array $data = [];

	private ?callable $submitHandler;

	public function __construct(?callable $callable) {
		$this->submitHandler = $callable;
	}

	/**
	 * @deprecated
	 * @see Player::sendForm()
	 */
	public function sendToPlayer(Player $player) : void {
		$player->sendForm($this);
	}

	public function getSubmitHandler() : ?callable {
		return $this->submitHandler;
	}

	public function setSubmitHandler(?callable $callable) : void {
		$this->submitHandler = $callable;
	}

	public function handleResponse(Player $player, $data) : void {
		$this->processData($data);
		$handler = $this->getSubmitHandler();
		if ($handler !== null) {
			$handler($player, $data);
		}
	}

	public function processData(&$data) : void {
	}

	public function jsonSerialize() : mixed {
		return $this->data;
	}
}
