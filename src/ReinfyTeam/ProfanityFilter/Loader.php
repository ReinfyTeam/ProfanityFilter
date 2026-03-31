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
use ReinfyTeam\ProfanityFilter\Command\ProfanityFilterCommand;
use ReinfyTeam\ProfanityFilter\Tasks\GithubUpdateTask;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use function array_filter;
use function array_values;
use function fclose;
use function file;
use function file_exists;
use function is_array;
use function is_string;
use function mkdir;
use function rename;
use function stream_get_contents;
use function unlink;
use function yaml_parse;

class Loader extends PluginBase {
	use SingletonTrait;

	public const FILTER_MODE_BLOCK = "block";
	public const FILTER_MODE_HIDE = "hide";
	public const PROVIDER_CUSTOM = "custom";

	private const DEFAULT_COMMAND_PERMISSION = "profanityfilter.command";
	private const DEFAULT_BYPASS_PERMISSION = "profanityfilter.bypass";
	private const CONFIG_VERSION_KEY = "config-version";
	private const PROFANITY_CONFIG_FILE = "profanity.yml";

	public static bool $isFilterEnabled = true;

	/** @var array<string, int> */
	public array $violationCounts = [];

	private ?Config $profanityConfig = null;
	/** @var string[]|null */
	private ?array $providedProfanityList = null;

	public function onLoad() : void {
		Loader::$instance = $this;
		$this->ensureConfigIsCurrent();
		$this->checkForUpdates();
		(new LanguageManager())->init();
		$this->initializeResources();
		$this->registerPermissions();
	}

	public function onEnable() : void {
		$this->registerCommands();
		$this->registerListeners();
	}

	private function ensureConfigIsCurrent() : void {
		$log = $this->getLogger();
		$pluginConfigResource = $this->getResource("config.yml");
		if ($pluginConfigResource === null) {
			$log->critical("Unable to read default config resource.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$lang = new LanguageManager();
		$pluginConfigContents = stream_get_contents($pluginConfigResource);
		if ($pluginConfigContents === false) {
			$log->critical("Unable to read default config contents.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			fclose($pluginConfigResource);
			return;
		}
		$pluginConfig = yaml_parse($pluginConfigContents);
		fclose($pluginConfigResource);
		$config = $this->getConfig();

		if (!is_array($pluginConfig)) {
			$log->critical("Invalid Configuration Syntax, Please remove your update the plugin.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if ($config->get(self::CONFIG_VERSION_KEY) === $pluginConfig[self::CONFIG_VERSION_KEY]) {
			return;
		}

		$log->notice($lang->translateMessage("outdated-config"));
		@rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "old-config.yml");
		@unlink($this->getDataFolder() . "old-config.yml");
		$this->saveResource("config.yml");
	}

	private function registerListeners() : void {
		$listener = $this->createChatListener();
		if ($listener === null) {
			return;
		}
		$this->getServer()->getPluginManager()->registerEvents($listener, $this);
	}

	private function createChatListener() : ?ChatProfanityListener {
		$provider = $this->getConfig()->get("profanity");
		$profanityProvider = is_string($provider) ? $provider : self::PROVIDER_CUSTOM;

		switch ($this->getConfig()->get("type")) {
			case self::FILTER_MODE_BLOCK:
				return new ChatProfanityListener(self::FILTER_MODE_BLOCK, $profanityProvider);
			case self::FILTER_MODE_HIDE:
				return new ChatProfanityListener(self::FILTER_MODE_HIDE, $profanityProvider);
			default:
				$this->handleInvalidFilterType();
				return null;
		}
	}

	private function handleInvalidFilterType() : void {
		$this->getLogger()->critical("Invalid Profanity Type. Please check instruction on your configuration.");
		$this->getServer()->getPluginManager()->disablePlugin($this);
	}

	private function registerCommands() : void {
		$this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new ProfanityFilterCommand());
	}

	private function checkForUpdates() : void {
		$lang = new LanguageManager();
		if ($this->getConfig()->get("check-updates")) {
			$this->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
		} else {
			$this->getServer()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("update-warning"));
		}
	}

	/**
	 * Get Profanity List. Do not call it directly.
	 */
	public function getProfanityConfig(bool $reload = false) : Config {
		if ($this->profanityConfig === null) {
			$this->profanityConfig = new Config($this->getDataFolder() . self::PROFANITY_CONFIG_FILE, Config::YAML);
		}
		if ($reload) {
			$this->profanityConfig->reload();
		}

		return $this->profanityConfig;
	}

	/**
	 * Initilize the resource in the context.
	 */
	private function initializeResources() : void {
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

	private function registerPermissions() : void {
		$commandPermission = $this->getConfig()->get("command-permission");
		$bypassPermission = $this->getConfig()->get("bypass-permission");
		$this->registerPermissionNode(is_string($commandPermission) ? $commandPermission : self::DEFAULT_COMMAND_PERMISSION);
		$this->registerPermissionNode(is_string($bypassPermission) ? $bypassPermission : self::DEFAULT_BYPASS_PERMISSION);
	}

	/**
	 * Register Permission on Plugin
	 * Custom Permission in Config.yml
	 * ---
	 * Introduced in v0.0.6-BETA
	 */
	private function registerPermissionNode(string $permissionName) : void {
		$permission = new Permission($permissionName);
		$permissionManager = PermissionManager::getInstance();
		$permissionManager->addPermission($permission);
		$root = $permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
		if ($root !== null) {
			$root->addChild($permission->getName(), true);
		}
	}

	/**
	 * @return string[]
	 */
	public function getProvidedProfanityList() : array {
		if ($this->providedProfanityList !== null) {
			return $this->providedProfanityList;
		}

		$path = $this->getDataFolder() . "profanity_filter.wlist";
		if (file_exists($path)) {
			$contents = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
			$strings = array_values(array_filter($contents, static fn($value) => is_string($value)));
			$this->providedProfanityList = $strings;
		} else {
			$this->providedProfanityList = ProfanityFilterService::getDefaultProfanityList();
		}

		return $this->providedProfanityList;
	}
}
