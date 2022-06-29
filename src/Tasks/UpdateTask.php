<?php

/*  					
 *			        _
 * 				  | |                  
 * __  ____ ___      _| |___  _____  _ __  
 * \ \/ / _` \ \ /\ / / __\ \/ / _ \| '_ \ 
 *  >  < (_| |\ V  V /| |_ >  < (_) | | | |
 * /_/\_\__, | \_/\_/  \__/_/\_\___/|_| |_|
 *         | |                             
 *         |_|                             
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
*/

declare(strict_types=1);

namespace ProfanityFilter\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use ProfanityFilter\Utils\Language;
use function is_array;
use function json_decode;
use function version_compare;
use function vsprintf;

class UpdateTask extends AsyncTask{

	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json?name=";

	public function __construct(private string $pluginName, private string $pluginVersion){
	     //NOOP
	}

	public function onRun() : void{
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName, 10, [], $err);
		$highestVersion = $this->pluginVersion;
		$artifactUrl = "";
		$api = "";
		if($json !== null){
			$releases = json_decode($json->getBody(), true);
			foreach($releases as $release){
				if(version_compare($highestVersion, $release["version"], ">=")){
				     continue;
				}
				$highestVersion = $release["version"];
				$artifactUrl = $release["artifact_url"];
				$api = $release["api"][0]["from"] . " - " . $release["api"][0]["to"];
			}
		}

		$this->setResult([$highestVersion, $artifactUrl, $api, $err]);
	}


	public function onCompletion() : void{
	     $lang = new Language();
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
		if($plugin === null){
			return;
		}

		[$highestVersion, $artifactUrl, $api, $err] = $this->getResult();
		if($err !== null){
			$plugin->getServer()->getLogger()->critical($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("update-error"), [$err]));
			return;
		}

		if($highestVersion !== $this->pluginVersion){
			$plugin->getServer()->getLogger()->error($lang->translateMessage("new-update-prefix") . " " . vsprintf($lang->translateMessage("new-update-found"), [$highestVersion, $api]));
		} else {
		     $plugin->getServer()->getLogger()->error($lang->translateMessage("new-update-prefix") . " " . $lang->translateMessage("no-updates-found"));
		}
	}
}

