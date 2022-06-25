<?php

/*  					
 *					   _
 * 					  | |                  
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
 * @author xqwtxib
 * @link http://xqwtxon.ml/
 *
*/

declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use pocketmine\utils\Internet;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\VersionInfo;
use function is_array;
use function json_decode;
use function version_compare;
use function vsprintf;

/*
 *	A class that updates plugin.
 * As of Sat 05/21/2022 10:49:29.36, we are now using asynctask method for updating plugin.
 */
class Updater extends AsyncTask {
	
	private const POGGIT_RELEASE_URL = "https://poggit.pmmp.io/releases.min.json?name=";
	private const GITHUB_RELEASE_URL = "https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/main/version.json";
	
	public function __construct(private string $pluginName, private string $pluginVersion){
		//NOOP
	}
	
	public function onRun() :void{
		$json = Internet::getUrl(self::POGGIT_RELEASE_URL . $this->pluginName, 10, [], $err);
		$currentVersion = $this->pluginVersion;
		$artifactURL = "";
		$api = "";
		if($json !== null){
			
			try {
				$release = json_decode($json->getBody(), true);
			}
			catch(Exception $error){
				$json = Internet::getUrl(self::GITHUB_RELEASE_URL, 10, [], $err);
				$release = json_decode($mirror->getBody(), true);
				if(version_compare($currentVersion, $release["version"], ">=")){
					//NOOP
				}
				$highestVersion = $release["version"];
				$artifactURL = $release["download"];
				$api = VersionInfo::BASE_VERSION . " to " . $release["api"];
			}
			
			foreach($releases as $release){
				if(version_compare($currentVersion, $release["version"], ">=")){
					//NOOP
				}
				$highestVersion = $release["version"];
				$artifactURL = $release["artifact_url"];
				$api = $release["api"][0]["from"] . " - " . $release["api"][0]["to"];
			}
		}
		
		$this->setResult([$highestVersion, $artifactURL, $api, $err]);
	}
	
	public function onCompletion() :void{
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
		if($plugin === null){
			return;
		}
		
		[$highestVersion, $artifactURL, $api, $err] = $this->getResult();
		if($err !== null){
			$plugin->getLogger()->error(vsprintf($this->lang->get("update-error"), [$err]));
			return;
		}
		
		if($highestVersion !== $this->pluginVersion){
			$plugin->getLogger()->notice(vsprintf($this->lang->get("new-update-found"), [$highestVersion, $api]));
			$plugin->getLogger()->notice(vsprintf($this->lang->get("new-update-details"), [$api]));
			$plugin->getLogger()->notice(vsprintf($this->lang->get("new-update-download"), [$artifactURL]));
		}
	}
}