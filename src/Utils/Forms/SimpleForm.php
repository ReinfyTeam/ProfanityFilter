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

class SimpleForm extends Form {

	const IMAGE_TYPE_PATH = 0;
	const IMAGE_TYPE_URL = 1;

	/** @var string */
	private $content = "";

	private $labelMap = [];

	/**
	 * @param callable|null $callable
	 */
	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["buttons"] = [];
	}

	public function processData(&$data): void {
		if ($data !== null) {
			if (!is_int($data)) {
				throw new FormValidationException("Expected an integer response, got " . gettype($data));
			}
			$count = count($this->data["buttons"]);
			if ($data >= $count || $data < 0) {
				throw new FormValidationException("Button $data does not exist");
			}
			$data = $this->labelMap[$data] ?? null;
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
	 * @param int $imageType
	 * @param string $imagePath
	 * @param string $label
	 */
	public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null): void {
		$content = ["text" => $text];
		if ($imageType !== -1) {
			$content["image"]["type"] = $imageType === 0 ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}
}
