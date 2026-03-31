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

namespace ReinfyTeam\ProfanityFilter\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use function count;
use function is_array;
use function json_decode;
use function version_compare;
use function vsprintf;

class PoggitUpdateTask extends AsyncTask {
	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json?name=";
	private const HTTP_TIMEOUT_SECONDS = 10;

	public function __construct(private string $pluginName, private string $pluginVersion) {
		//NOOP
	}

	public function onRun() : void {
		$this->setResult($this->fetchReleases());
	}

	public function onCompletion() : void {
		$lang = new LanguageManager();
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
		if (!$plugin instanceof Loader) {
			return;
		}
		$result = $this->getResult();
		if (!is_array($result) || count($result) !== 5) {
			return;
		}
		[$highestVersion, $artifactUrl, $apiFrom, $apiTo, $error] = $result;
		if ($highestVersion === null || $artifactUrl === null || $apiFrom === null || $apiTo === null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), ["Trying to update on github..."]));
			$plugin->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask($plugin->getDescription()->getName(), $plugin->getDescription()->getVersion()));
			return;
		} // Issue: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
		if ($error !== null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), [(string) $error]));
			Server::getInstance()->getLogger()->notice($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("update-retry"));
			$plugin->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask($plugin->getDescription()->getName(), $plugin->getDescription()->getVersion()));
			return;
		}

		if ($highestVersion !== $this->pluginVersion) {
			Server::getInstance()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("new-update-found"), [$highestVersion, $apiFrom]));
			Server::getInstance()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("new-update-details"), [$apiFrom, $apiTo]));
			Server::getInstance()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("new-update-download"), [$artifactUrl]));
		} else {
			Server::getInstance()->getLogger()->notice($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("no-updates-found"));
		}
	}

	/**
	 * @return array{0: string|null, 1: string|null, 2: string|null, 3: string|null, 4: string|null}
	 */
	private function fetchReleases() : array {
		$error = null;
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName, self::HTTP_TIMEOUT_SECONDS, [], $error);
		$highestVersion = $this->pluginVersion;
		$artifactUrl = "";
		$apiFrom = null;
		$apiTo = null;

		if ($json !== null) {
			$releases = json_decode($json->getBody(), true);
			if (!is_array($releases)) {
				$error = "json_decode() parse failed. Is the result is not json type or has a syntax error?"; // v0.1.2 (json_decode() failes fix)
				return [null, null, null, null, $error];
			} // Issue Fix: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
			foreach ($releases as $release) {
				if (!is_array($release) || !isset($release["version"])) {
					continue;
				}
				$releaseVersion = (string) $release["version"];
				if (version_compare($highestVersion, $releaseVersion, ">=")) {
					continue;
				}
				$highestVersion = $releaseVersion;
				$artifactUrl = (string) ($release["artifact_url"] ?? $artifactUrl);
				$apiFrom = isset($release["api"][0]["from"]) ? (string) $release["api"][0]["from"] : $apiFrom;
				$apiTo = isset($release["api"][0]["to"]) ? (string) $release["api"][0]["to"] : $apiTo;
			}
		}

		return [$highestVersion, $artifactUrl, $apiFrom, $apiTo, $error];
	}
}
