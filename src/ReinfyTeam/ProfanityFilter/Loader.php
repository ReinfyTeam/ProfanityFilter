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

namespace ReinfyTeam\ProfanityFilter;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use ReinfyTeam\ProfanityFilter\Command\DefaultCommand;
use ReinfyTeam\ProfanityFilter\Tasks\PoggitUpdateTask;
use ReinfyTeam\ProfanityFilter\Utils\Language;
use function fclose;
use function file;
use function file_exists;
use function mkdir;
use function rename;
use function stream_get_contents;
use function unlink;
use function yaml_parse;

class Loader extends PluginBase {
	use SingletonTrait;

	public static bool $enabled = true;

	public array $punishment = [];

	public function onLoad() : void {
		Loader::$instance = $this;
		$this->checkConfig();
		$this->checkUpdate();
		(new Language())->init();
		$this->saveResources();
		$this->loadPermission();
	}

	public function onEnable() : void {
		$this->registerCommands();
		$this->loadListeners();
	}

	private function checkConfig() : void {
		$log = $this->getLogger();
		$pluginConfigResource = $this->getResource("config.yml");
		$lang = new Language();
		$pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
		fclose($pluginConfigResource);
		$config = $this->getConfig();

		if ($pluginConfig == false) {
			$log->critical("Invalid Configuration Syntax, Please remove your update the plugin.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if ($config->get("config-version") === $pluginConfig["config-version"]) {
			return;
		}

		$log->notice($lang->translateMessage("outdated-config"));
		@rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "old-config.yml");
		@unlink($this->getDataFolder() . "old-config.yml");
		$this->saveResource("config.yml");
	}

	private function loadListeners() : void {
		switch ($this->getConfig()->get("type")) {
			case "block":
				$this->getServer()->getPluginManager()->registerEvents(new EventListener("block", $this->getConfig()->get("profanity")), $this);
				break;
			case "hide":
				$this->getServer()->getPluginManager()->registerEvents(new EventListener("hide", $this->getConfig()->get("profanity")), $this);
				break;
			default:
				$this->getLogger()->critical("Invalid Profanity Type. Please check instruction on your configuration.");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				break;
		}
	}

	private function registerCommands() : void {
		$this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new DefaultCommand());
	}

	private function checkUpdate() : void {
		$lang = new Language();
		if ($this->getConfig()->get("check-updates")) {
			$this->getServer()->getAsyncPool()->submitTask(new PoggitUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
		} else {
			$this->getServer()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("update-warning"));
		}
	}

	/**
	 * Get Profanity List. Do not call it directly.
	 */
	public function getProfanity() : Config {
		return new Config($this->getDataFolder() . "profanity.yml", Config::YAML);
	}

	/**
	 * Initilize the resource in the context.
	 */
	private function saveResources() : void {
		if (!file_exists($this->getDataFolder() . "languages/")) {
			@mkdir($this->getDataFolder() . "languages/");
		}
		$this->saveResource("languages/eng.yml");
		if (!file_exists($this->getDataFolder() . "banned-words.yml")) {
			$this->saveResource("banned-words.yml");
		}

		foreach ($this->getResources() as $file) {
			$this->saveResource($file->getFilename());
		}
	}

	private function loadPermission() : void {
		$this->registerPermission(($this->getConfig()->get("command-permission") ?? "profanityfilter.command"));
		$this->registerPermission(($this->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"));
	}

	/**
	 * Register Permission on Plugin
	 * Custom Permission in Config.yml
	 * ---
	 * Introduced in v0.0.6-BETA
	 */
	private function registerPermission(string $perm) : void {
		$permission = new Permission($perm);
		$permManager = PermissionManager::getInstance();
		$permManager->addPermission($permission);
		$permManager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild($permission->getName(), true);
	}

	public function getProvidedProfanities() : array {
		return file($this->getDataFolder() . "profanity_filter.wlist");
	}
}
