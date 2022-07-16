<?php

/**  					
 *			        _
 * 				  | |                  
 * __  ____ ___      _| |___  _____  _ __  
 * \ \/ / _` \ \ /\ / / __\ \/ / _ \| '_ \ 
 *  >  < (_| |\ V  V /| |_ >  < (_) | | | |
 * /_/\_\__, | \_/\_/  \__/_/\_\___/|_| |_|
 *         | |                             
 *         |_|                             
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
 */

declare(strict_types=1);

namespace xqwtxon\ProfanityFilter\Utils\Forms;

use pocketmine\form\FormValidationException;

class ModalForm extends Form {

	/** @var string */
	private $content = "";

	/**
	 * @param callable|null $callable
	 */
	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "modal";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["button1"] = "";
		$this->data["button2"] = "";
	}

	public function processData(&$data): void {
		if (!is_bool($data)) {
			throw new FormValidationException("Expected a boolean response, got " . gettype($data));
		}
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void {
		$this->data["title"] = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->data["title"];
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->data["content"];
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void {
		$this->data["content"] = $content;
	}

	/**
	 * @param string $text
	 */
	public function setButton1(string $text): void {
		$this->data["button1"] = $text;
	}

	/**
	 * @return string
	 */
	public function getButton1(): string {
		return $this->data["button1"];
	}

	/**
	 * @param string $text
	 */
	public function setButton2(string $text): void {
		$this->data["button2"] = $text;
	}

	/**
	 * @return string
	 */
	public function getButton2(): string {
		return $this->data["button2"];
	}
}
