<?php

if (ini_get("phar.readonly") === "1") {
    echo "[!] Set phar.readonly to 0 with -dphar.readonly=0" . PHP_EOL;
    exit(1);
}

$pharPath = getcwd() . DIRECTORY_SEPARATOR . basename(__DIR__) . ".phar";
echo "Creating output file $pharPath" . PHP_EOL;

if (file_exists($pharPath)) {
    echo "Phar file already exists, overwriting..." . PHP_EOL;
    Phar::unlinkArchive($pharPath);
}

echo "Adding files..." . PHP_EOL;

$start = microtime(true);
$phar = new Phar($pharPath);
$phar->startBuffering();

/**
 * The following lines originated from https://github.com/pmmp/PocketMine-MP/blob/stable/build/server-phar.php
 */
$dir = rtrim(str_replace("/", DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$regex = sprintf('/^%s(%s).*/i',
    //String must start with this path...
    preg_quote($dir, '/'),
    //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
    implode('|', array_map(static function(string $string) : string { return preg_quote($string, '/'); }, ["src", "resources", "plugin.yml", ".poggit.yml"]))
);

$directory = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
$iterator = new \RecursiveIteratorIterator($directory);
$regexIterator = new \RegexIterator($iterator, $regex);

$count = count($phar->buildFromIterator($regexIterator, $dir));
echo "Added " . $count . " files" . PHP_EOL;

echo "Compressing files..." . PHP_EOL;
$phar->compressFiles(Phar::GZ);
echo "Finished compression" . PHP_EOL;
$phar->stopBuffering();

if (isset($argv[1])) {
    if (strtolower($argv[1]) === "--no-virions") {
        echo "Skipped virion injection" . PHP_EOL;
    } else if (strtolower($argv[1]) === "--keep-virions") {
        echo "Virions will be kept and won't be deleted after they have been injected." . PHP_EOL;
        injectVirions(true);
    } else {
        injectVirions(false);
    }
} else {
    injectVirions(false);
}

echo "Done in " . round(microtime(true) - $start, 3) . "s" . PHP_EOL;
exit();

/**
 * The code used in this function was copied and modified from https://github.com/poggit/devirion/blob/master/cli.php
 */
function injectVirions(bool $keepVirions) : void {
    $manifest = getcwd() . DIRECTORY_SEPARATOR . ".poggit.yml";
    if (!is_file($manifest)) {
        return;
    }
    if (!function_exists("yaml_parse")) {
        echo "[!] Skipping virion injection: YAML extension is required to inject virions into the phar" . PHP_EOL;
        return;
    }
    $data = yaml_parse(file_get_contents($manifest));
    if (!is_array($data) || !isset($data["projects"])) {
        echo "[!] Skipping virion injection: Invalid .poggit.yml file" . PHP_EOL;
        return;
    }
    $projects = array_change_key_case($data["projects"], CASE_LOWER);
    $projectName = strtolower(basename(__DIR__));
    if (!isset($projects[$projectName])) {
        echo "[!] Skipping virion injection: Project " . $projectName . " not found in .poggit.yml" . PHP_EOL;
        return;
    }
    $project = $projects[$projectName];
    if (!isset($project["libs"]) || !is_array($project["libs"])) {
        return;
    }
    $targetFolder = getcwd() . DIRECTORY_SEPARATOR . "virions";
    if (!is_dir($targetFolder)) {
        mkdir($targetFolder);
    }
    $targetFolder = rtrim($targetFolder, "\\/") . "/";
    $injectedVirions = [];
    $deletedVirions = [];
    foreach ($project["libs"] as $n => $lib) {
        if (isset($lib["format"]) && $lib["format"] !== "virion") {
            echo "[!] Warning: Not processing library #$n because it is not in virion format:\n  ", str_replace("\n", "\n  ", yaml_emit($lib)) . PHP_EOL;
            continue;
        }
        if(!isset($lib["src"])){
            echo "[!] Library #$n does not contain src: attribute" . PHP_EOL;
        }

        $src = $lib["src"];
        $vendor = strtolower($libDeclaration["vendor"] ?? "poggit-project");
        if($vendor === "raw"){
            if (str_starts_with($src, "http://") || str_starts_with($src, "https://")) {
                $file = $src;
            }else{
                $file = $manifest . "/../";
                if($src[0] === "/"){
                    if(($projectPath = trim($project["path"], "/")) !== ''){
                        $file .= $projectPath . "/";
                    }
                    $src = substr($src, 1);
                }
                $file .= $src;
            }
        }else{
            if($vendor !== "poggit-project"){
                echo "[!] For library #$n, unknown vendor $vendor, assumed 'poggit-project'" . PHP_EOL;
            }

            if (!isset($lib["src"]) || count($srcParts = explode("/", trim($lib["src"], " \t\n\r\0\x0B/"))) === 0) {
                echo "[!] For library #$n, 'src' attribute is missing, skipping" . PHP_EOL;
                continue;
            }
            $srcProject = array_pop($srcParts);
            $srcRepo = array_pop($srcParts) ?? $project->repo[1];
            $srcOwner = array_pop($srcParts) ?? $project->repo[0];

            if (file_exists($targetFolder . $srcProject . ".phar")) {
                echo "[!] Found existing virion phar for " . $srcProject . ". Using this file for injection. (Always make sure that that file is up to date!)"  . PHP_EOL;
                exec(PHP_BINARY . " " . $targetFolder . $srcProject . ".phar" . " " . getcwd() . DIRECTORY_SEPARATOR . basename(__DIR__) . ".phar");
                $injectedVirions[] = $srcProject;
                continue;
            }

            $version = $lib["version"] ?? "*";
            $branch = $libDeclaration["branch"] ?? ":default";

            $file = "https://poggit.pmmp.io/v.dl/$srcOwner/$srcRepo/" . urlencode($srcProject) . "/" . urlencode($version) . "?branch=" . urlencode($branch);
        }

        $url = @fopen($file, "rb", false, stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]));
        if ($url === false) {
            continue;
        }
        $tmpStream = mkstemp("virion_tmp_XXXXXX.phar", $tmpFile);
        stream_copy_to_stream($url, $tmpStream);
        fclose($tmpStream);
        fclose($url);
        try{
            $phar = new Phar($tmpFile);
            $virionYml = yaml_parse(file_get_contents((string) $phar["virion.yml"]));
            if(!is_array($virionYml) || !isset($virionYml["name"], $virionYml["version"])){
                echo "[!] Skipping virion injection: For library #$n, the phar file at $file is not a valid virion." . PHP_EOL;
                continue;
            }
            $targetFile = $targetFolder . $virionYml["name"] . ".phar";
            copy($tmpFile, $targetFile);
            exec(PHP_BINARY . " " . $targetFile . " " . getcwd() . DIRECTORY_SEPARATOR . basename(__DIR__) . ".phar");
            $injectedVirions[] = $virionYml["name"];
            if (!$keepVirions) {
                unlink($targetFile);
                $deletedVirions[] = $virionYml["name"];
            }
        } catch (UnexpectedValueException $ex) {
            echo "[!] Skipping virion injection: A corrupted phar file was downloaded for library #$n ({$ex->getMessage()})." . PHP_EOL;
        }
        unlink($tmpFile);
    }
    if (count($injectedVirions) === count($deletedVirions)) {
        rmdir($targetFolder);
        echo "Successfully injected (and deleted) the following virions into " . basename(__DIR__) . ".phar: " . implode(", ", $injectedVirions) . PHP_EOL;
    } else {
        echo "Successfully injected the following virions into " . basename(__DIR__) . ".phar: " . implode(", ", $injectedVirions) . PHP_EOL;
    }
}

/**
 * @param $template
 * @param &$randomFile
 *
 * @return bool|resource
 *
 * This function was copied (and slightly modified) from https://github.com/poggit/devirion/blob/master/cli.php, but the original author is:
 * @author mobius https://stackoverflow.com/a/8971248/3990767
 */
function mkstemp($template, &$randomFile) {
    $attempts = 238328; // 62 x 62 x 62
    $letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $length = strlen($letters) - 1;

    if(strlen($template) < 6 || !str_contains($template, "XXXXXX")){
        return false;
    }

    for($count = 0; $count < $attempts; ++$count){
        $random = "";

        for($p = 0; $p < 6; $p++){
            $random .= $letters[mt_rand(0, $length)];
        }

        $randomFile = str_replace("XXXXXX", $random, $template);

        if(!($fd = @fopen($randomFile, "x+b"))){
            continue;
        }

        return $fd;
    }

    return false;
}