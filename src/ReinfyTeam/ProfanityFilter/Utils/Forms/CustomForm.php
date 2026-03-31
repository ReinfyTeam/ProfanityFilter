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
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

class CustomForm extends Form {
	private const UNSET_INDEX = -1;

	private array $labelMap = [];

	private array $validationMethods = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "custom_form";
		$this->data["title"] = "";
		$this->data["content"] = [];
	}

	public function processData(&$data) : void {
		if ($data !== null && !is_array($data)) {
			throw new FormValidationException("Expected an array response, got " . gettype($data));
		}
		if (is_array($data)) {
			if (count($data) !== count($this->validationMethods)) {
				throw new FormValidationException("Expected an array response with the size " . count($this->validationMethods) . ", got " . count($data));
			}
			$new = [];
			foreach ($data as $i => $v) {
				$validationMethod = $this->validationMethods[$i] ?? null;
				if ($validationMethod === null) {
					throw new FormValidationException("Invalid element " . $i);
				}
				if (!$validationMethod($v)) {
					throw new FormValidationException("Invalid type given for element " . $this->labelMap[$i]);
				}
				$new[$this->labelMap[$i]] = $v;
			}
			$data = $new;
		}
	}

	public function setTitle(string $title) : void {
		$this->data["title"] = $title;
	}

	public function getTitle() : string {
		return $this->data["title"];
	}

	public function addLabel(string $text, ?string $label = null) : void {
		$this->addElement(["type" => "label", "text" => $text], static fn($v) => $v === null, $label);
	}

	public function addToggle(string $text, bool $default = null, ?string $label = null) : void {
		$content = ["type" => "toggle", "text" => $text];
		if ($default !== null) {
			$content["default"] = $default;
		}
		$this->addElement($content, static fn($v) => is_bool($v), $label);
	}

	public function addSlider(string $text, int $min, int $max, int $step = self::UNSET_INDEX, int $default = self::UNSET_INDEX, ?string $label = null) : void {
		$content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
		if ($step !== self::UNSET_INDEX) {
			$content["step"] = $step;
		}
		if ($default !== self::UNSET_INDEX) {
			$content["default"] = $default;
		}
		$this->addElement($content, static fn($v) => (is_float($v) || is_int($v)) && $v >= $min && $v <= $max, $label);
	}

	public function addStepSlider(string $text, array $steps, int $defaultIndex = self::UNSET_INDEX, ?string $label = null) : void {
		$content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
		if ($defaultIndex !== self::UNSET_INDEX) {
			$content["default"] = $defaultIndex;
		}
		$this->addElement($content, static fn($v) => is_int($v) && isset($steps[$v]), $label);
	}

	public function addDropdown(string $text, array $options, int $default = null, ?string $label = null) : void {
		$this->addElement(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default], static fn($v) => is_int($v) && isset($options[$v]), $label);
	}

	public function addInput(string $text, string $placeholder = "", string $default = null, ?string $label = null) : void {
		$this->addElement(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default], static fn($v) => is_string($v), $label);
	}

	private function addContent(array $content) : void {
		$this->data["content"][] = $content;
	}

	private function addElement(array $content, callable $validator, ?string $label = null) : void {
		$this->addContent($content);
		$this->labelMap[] = $label ?? count($this->labelMap);
		$this->validationMethods[] = $validator;
	}
}
