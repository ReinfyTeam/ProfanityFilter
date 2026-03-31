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
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use function json_decode;
use function vsprintf;

class GithubUpdateTask extends AsyncTask {
	private const GIT_URL = "https://raw.githubusercontent.com/ReinfyTeam/ProfanityFilter/stable/build_info.json";
	private const HTTP_TIMEOUT_SECONDS = 10;

	public function __construct(private string $pluginName, private string $pluginVersion) {
		//NOOP
	}

	public function onRun() : void {
		$this->setResult($this->fetchLatest());
	}

	public function onCompletion() : void {
		$lang = new LanguageManager();
		[$highestVersion, $artifactUrl, $apiTo, $error, $apiFrom] = $this->getResult();
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
		if ($plugin === null) {
			return;
		}

		if ($error !== null) {
			Server::getInstance()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), [$error]));
			//Server::getInstance()->getLogger()->notice($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("update-retry-failed"));
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

	private function fetchLatest() : array {
		$error = null;
		$json = Internet::getURL(self::GIT_URL, self::HTTP_TIMEOUT_SECONDS, [], $error);
		if ($error !== null) {
			return ["", "", "", $error, ""];
		}

		$releases = json_decode($json->getBody(), true);
		if ($releases === null) {
			$errorMessage = "json_decode() parse failed. Is the result is not json type or has a syntax error?"; // v0.1.2 (json_decode() failes fix)
			return ["", "", "", $errorMessage, ""];
		}

		return [
			$releases["version"],
			$releases["artifactUrl"],
			$releases["api_to"],
			null,
			$releases["api_from"],
		];
	}
}
