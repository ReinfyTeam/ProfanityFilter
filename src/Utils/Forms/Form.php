<?php

/*
 *
 * __  __   __ _  __      __ | |_  __  __   ___    _ __
 * \ \/ /  / _` | \ \ /\ / / | __| \ \/ /  / _ \  | '_ \
 *  >  <  | (_| |  \ V  V /  | |_   >  <  | (_) | | | | |
 * /_/\_\  \__, |   \_/\_/    \__| /_/\_\  \___/  |_| |_|
 *            |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
 *
 */

declare(strict_types=1);

namespace xqwtxon\ProfanityFilter\Utils\Forms;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm {
	protected array $data = [];

	/** @var callable|null */
	private $callable;

	public function __construct(?callable $callable) {
		$this->callable = $callable;
	}

	/**
	 * @deprecated
	 * @see Player::sendForm()
	 */
	public function sendToPlayer(Player $player) : void {
		$player->sendForm($this);
	}

	public function getCallable() : ?callable {
		return $this->callable;
	}

	public function setCallable(?callable $callable) {
		$this->callable = $callable;
	}

	public function handleResponse(Player $player, $data) : void {
		$this->processData($data);
		$callable = $this->getCallable();
		if ($callable !== null) {
			$callable($player, $data);
		}
	}

	public function processData(&$data) : void {
	}

	public function jsonSerialize() {
		return $this->data;
	}
}
