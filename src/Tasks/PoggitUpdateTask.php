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
use ReinfyTeam\ProfanityFilter\Utils\Language;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\Tasks\GithubUpdateTask;
use function json_decode;
use function version_compare;
use function vsprintf;

class PoggitUpdateTask extends AsyncTask {
	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json?name=";

	public function __construct(private string $pluginName, private string $pluginVersion) {
		//NOOP
	}

	public function onRun() : void {
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName, 10, [], $err);
		$highestVersion = $this->pluginVersion;
		$artifactUrl = "";
		$api = "";
		if ($json !== null) {
			$releases = json_decode($json->getBody(), true);
			if ($releases === null) {
				return;
			} // Issue Fix: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
			foreach ($releases as $release) {
				if (version_compare($highestVersion, $release["version"], ">=")) {
					continue;
				}
				$highestVersion = $release["version"];
				$artifactUrl = $release["artifact_url"];
				$api = $release["api"][0]["from"] . " - " . $release["api"][0]["to"];
			}
		}

		$this->setResult([$highestVersion, $artifactUrl, $api, $err]);
	}

	public function onCompletion() : void {
		$lang = new Language();
		[$highestVersion, $artifactUrl, $api, $err] = $this->getResult();
		if ($highestVersion === null || $artifactUrl === null || $api === null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), ["Trying to update on github..."]));
			Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask(Loader::getInstance()->getDescription()->getName(), Loader::getInstance()->getDescription()->getVersion()));
			return;
		} // Issue: https://github.com/ReinfyTeam/ProfanityFilter/issues/107
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
		if ($plugin === null) {
			return;
		}
		if ($err !== null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), ["Trying to update on github..."]));
			Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new GithubUpdateTask(Loader::getInstance()->getDescription()->getName(), Loader::getInstance()->getDescription()->getVersion()));
			return;
		}

		if ($highestVersion !== $this->pluginVersion) {
			Server::getInstance()->getLogger()->warning($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("new-update-found"), [$highestVersion, $api]));
		} else {
			Server::getInstance()->getLogger()->notice($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("no-updates-found"));
		}
	}
}
