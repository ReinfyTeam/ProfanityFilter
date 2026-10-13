<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$source = $projectRoot . "/vendor/axolotl-pm/pocketmine-mp/composer.json";
$targetDirectory = $projectRoot . "/vendor/pocketmine/pocketmine-mp";
$target = $targetDirectory . "/composer.json";

if (!is_file($source)) {
	throw new RuntimeException("Axolotl-PM Composer metadata is missing: {$source}");
}

if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
	throw new RuntimeException("Unable to create Pharynx compatibility directory: {$targetDirectory}");
}

if (!copy($source, $target)) {
	throw new RuntimeException("Unable to expose Axolotl-PM metadata to Pharynx: {$target}");
}

fwrite(STDOUT, "Prepared Pharynx metadata compatibility path.\n");
