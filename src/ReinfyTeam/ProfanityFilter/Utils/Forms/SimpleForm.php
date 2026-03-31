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

use pocketmine\form\FormValidationException;
use function count;
use function gettype;
use function is_int;

class SimpleForm extends Form {
	const IMAGE_TYPE_PATH = 0;
	const IMAGE_TYPE_URL = 1;
	const IMAGE_TYPE_NONE = -1;

	private string $content = "";

	/** @var array<int, string|int|null> */
	private array $labelMap = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["buttons"] = [];
	}

	public function processData(mixed &$data) : void {
		if ($data !== null) {
			if (!is_int($data)) {
				throw new FormValidationException("Expected an integer response, got " . gettype($data));
			}
			/** @var array<int, array<string, mixed>> $buttons */
			$buttons = $this->data["buttons"];
			$count = count($buttons);
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
		$title = $this->data["title"] ?? "";
		return is_string($title) ? $title : "";
	}

	public function getContent() : string {
		$content = $this->data["content"] ?? "";
		return is_string($content) ? $content : "";
	}

	public function setContent(string $content) : void {
		$this->data["content"] = $content;
	}

	public function addButton(string $text, int $imageType = self::IMAGE_TYPE_NONE, string $imagePath = "", ?string $label = null) : void {
		$content = ["text" => $text];
		if ($imageType !== self::IMAGE_TYPE_NONE) {
			$content["image"] = $this->buildImage($imageType, $imagePath);
		}
		/** @var array<int, array<string, mixed>> $buttons */
		$buttons = $this->data["buttons"];
		$buttons[] = $content;
		$this->data["buttons"] = $buttons;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	/**
	 * @return array{type: string, data: string}
	 */
	private function buildImage(int $imageType, string $imagePath) : array {
		return [
			"type" => $imageType === self::IMAGE_TYPE_PATH ? "path" : "url",
			"data" => $imagePath,
		];
	}
}
