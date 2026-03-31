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
		if ($plugin === null) {
			return;
		}
		[$highestVersion, $artifactUrl, $apiFrom, $apiTo, $error] = $this->getResult();
		if ($highestVersion === null || $artifactUrl === null || $apiFrom === null || $apiTo === null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), ["Trying to update on github..."]));
			$plugin->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask(Loader::getInstance()->getDescription()->getName(), Loader::getInstance()->getDescription()->getVersion()));
			return;
		} // Issue: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
		if ($error !== null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), [$error]));
			Server::getInstance()->getLogger()->notice($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("update-retry"));
			$plugin->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask(Loader::getInstance()->getDescription()->getName(), Loader::getInstance()->getDescription()->getVersion()));
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

	private function fetchReleases() : array {
		$error = null;
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName, self::HTTP_TIMEOUT_SECONDS, [], $error);
		$highestVersion = $this->pluginVersion;
		$artifactUrl = "";
		$apiFrom = null;
		$apiTo = null;

		if ($json !== null) {
			$releases = json_decode($json->getBody(), true);
			if ($releases === null) {
				$error["json_decode() parse failed. Is the result is not json type or has a syntax error?"]; // v0.1.2 (json_decode() failes fix)
				return [null, null, null, null, $error];
			} // Issue Fix: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
			foreach ($releases as $release) {
				if (version_compare($highestVersion, $release["version"], ">=")) {
					continue;
				}
				$highestVersion = $release["version"];
				$artifactUrl = $release["artifact_url"];
				$apiFrom = $release["api"][0]["from"];
				$apiTo = $release["api"][0]["to"];
			}
		}

		return [$highestVersion, $artifactUrl, $apiFrom, $apiTo, $error];
	}
}
