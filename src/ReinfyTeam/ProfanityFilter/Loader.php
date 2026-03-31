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

use CortexPE\Commando\PacketHooker;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use ReinfyTeam\ProfanityFilter\Command\ProfanityFilterCommand;
use ReinfyTeam\ProfanityFilter\Tasks\GithubUpdateTask;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;
use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function file_exists;
use function file_put_contents;
use function is_array;
use function is_string;
use function is_dir;
use function mkdir;
use function rename;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;

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

	private LanguageManager $language;

	private ?Config $profanityConfig = null;
	/** @var string[]|null */
	private ?array $providedProfanityList = null;

	public function onLoad() : void {
		Loader::$instance = $this;
		$this->language = new LanguageManager();
		$this->initializeResources();
		$this->language->init();
		$this->ensureConfigIsCurrent();
		$this->registerPermissions();
		$this->checkForUpdates();
	}

	public function onEnable() : void {
		$this->registerPacketHooker();
		$this->registerCommands();
		$this->registerListeners();
	}

	private function ensureConfigIsCurrent() : void {
		$log = $this->getLogger();
		$dataFolder = $this->getDataFolder();
		if (!is_dir($dataFolder)) {
			@mkdir($dataFolder, 0777, true);
		}

		$pluginConfigContents = $this->getResourceContents("config.yml");
		if ($pluginConfigContents === null) {
			$log->critical("Unable to read default config resource.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		$pluginConfig = $this->parseYamlString($pluginConfigContents);
		$config = $this->getConfig();

		if (!is_array($pluginConfig)) {
			$log->critical("Invalid Configuration Syntax, Please remove your update the plugin.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if ($config->get(self::CONFIG_VERSION_KEY) === $pluginConfig[self::CONFIG_VERSION_KEY]) {
			return;
		}

		$log->notice($this->language->translateMessage("outdated-config"));
		if (file_exists($dataFolder . "config.yml")) {
			@rename($dataFolder . "config.yml", $dataFolder . "old-config.yml");
		}
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
		$this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new ProfanityFilterCommand($this));
	}

	private function registerPacketHooker() : void {
		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}
	}

	private function checkForUpdates() : void {
		if ($this->getConfig()->get("check-updates")) {
			$this->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
		} else {
			$this->getServer()->getLogger()->debug($this->language->translateMessage("new-update-prefix") . " " . $this->language->translateMessage("update-warning"));
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
		$dataFolder = $this->getDataFolder();
		$langFolder = $dataFolder . "languages/";
		if (!file_exists($langFolder)) {
			@mkdir($langFolder);
		}
		$this->saveResource("languages/eng.yml");
		// Keep the default profanity wordlist immutable by resetting it on every load.
		$this->saveResource("profanity_filter.wlist", true);
		if (!file_exists($dataFolder . "banned-words.yml")) {
			$this->saveResource("banned-words.yml");
		}

		foreach ($this->getResources() as $file) {
			$this->saveResource($file->getFilename());
		}

		// Ensure custom profanity list never duplicates the provided defaults.
		PluginUtils::sanitizeCustomProfanityList();
	}

	private function registerPermissions() : void {
		$this->registerPermissionNode($this->getConfigString("command-permission", self::DEFAULT_COMMAND_PERMISSION));
		$this->registerPermissionNode($this->getConfigString("bypass-permission", self::DEFAULT_BYPASS_PERMISSION));
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

		$contents = $this->getResourceContents("profanity_filter.wlist");
		if ($contents !== null) {
			$lines = array_values(
				array_filter(
					array_map(static fn(string $line) : string => trim($line), explode("\n", $contents)),
					static fn(string $value) : bool => $value !== ""
				)
			);
			$this->providedProfanityList = $lines;
			return $this->providedProfanityList;
		}

		// Fallback to the smaller built-in list when resource reading fails.
		$this->providedProfanityList = ProfanityFilterService::getDefaultProfanityList();

		return $this->providedProfanityList;
	}

	private function getResourceContents(string $filename) : ?string {
		foreach ($this->getResources() as $resource) {
			if ($resource->getFilename() !== $filename) {
				continue;
			}
			$file = $resource->openFile("r");
			$content = $file->fread($resource->getSize());
			return $content === false ? null : $content;
		}

		return null;
	}

	/**
	 * @return array<int|string, mixed>|null
	 */
	private function parseYamlString(string $yaml) : ?array {
		$tempFile = tempnam(sys_get_temp_dir(), "pf-default-config-");
		if ($tempFile === false) {
			return null;
		}
		$written = file_put_contents($tempFile, $yaml);
		if ($written === false) {
			@unlink($tempFile);
			return null;
		}

		$parsed = (new Config($tempFile, Config::YAML))->getAll();
		@unlink($tempFile);

		return $parsed;
	}

	private function getConfigString(string $key, string $default) : string {
		$value = $this->getConfig()->get($key);
		if (!is_string($value)) {
			return $default;
		}

		return $value;
	}
}
