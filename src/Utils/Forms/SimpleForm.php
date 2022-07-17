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

use pocketmine\form\FormValidationException;
use function count;
use function gettype;
use function is_int;

class SimpleForm extends Form {
	const IMAGE_TYPE_PATH = 0;

	const IMAGE_TYPE_URL = 1;

	private string $content = "";

	private array $labelMap = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["buttons"] = [];
	}

	public function processData(&$data) : void {
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

	public function setTitle(string $title) : void {
		$this->data["title"] = $title;
	}

	public function getTitle() : string {
		return $this->data["title"];
	}

	public function getContent() : string {
		return $this->data["content"];
	}

	public function setContent(string $content) : void {
		$this->data["content"] = $content;
	}

	/**
	 * @param string $label
	 */
	public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null) : void {
		$content = ["text" => $text];
		if ($imageType !== -1) {
			$content["image"]["type"] = $imageType === 0 ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}
}
