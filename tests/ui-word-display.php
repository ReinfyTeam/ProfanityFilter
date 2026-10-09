<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

if (isset($argv[1])) {
	$sourceRoot = $argv[1];
	if (!is_dir($sourceRoot)) {
		throw new RuntimeException("The compiled source directory does not exist");
	}
	spl_autoload_register(static function (string $class) use ($sourceRoot) : void {
		$file = $sourceRoot . "/" . str_replace("\\", "/", $class) . ".php";
		if (is_file($file)) {
			require $file;
		}
	}, true, true);
}

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ProfanityFilter\Command\SubCommand\GuiSubCommand;
use ReinfyTeam\ProfanityFilter\Loader;
use ReinfyTeam\ProfanityFilter\ProfanityFilterService;
use ReinfyTeam\ProfanityFilter\Utils\LanguageManager;
use ReinfyTeam\ProfanityFilter\Utils\PluginUtils;

final class DisplayTestLoader extends Loader {
	public Config $words;

	public function getConfig() : Config {
		return $this->words;
	}

	public function getProfanityConfig(bool $reload = false) : Config {
		if ($reload) {
			$this->words->reload();
		}
		return $this->words;
	}

	public function getProvidedProfanityList() : array {
		return [];
	}
}

final class DisplayTestLanguage extends LanguageManager {
	public function __construct() {
	}

	public function translateMessage(string $option) : string {
		return $option;
	}
}

final class DisplayTestGui extends GuiSubCommand {
	public function __construct(Loader $loader) {
		$this->loader = $loader;
		$this->language = new DisplayTestLanguage();
	}
}

final class DisplayTestPlayer extends Player {
	public Form $lastForm;

	public function __construct() {
	}

	public function __destruct() {
	}

	public function sendForm(Form $form) : void {
		$this->lastForm = $form;
	}
}

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks) : void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
	++$checks;
};
$payload = static fn(Form $form) : array => json_decode(json_encode($form, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
$path = tempnam(sys_get_temp_dir(), "pf-ui-");
if ($path === false) {
	throw new RuntimeException("Unable to create a temporary config");
}

try {
	$raw = ["fuck", 123, "motherfucker", "Địt", "e\u{0301}vil", "中文", "bad/word", "👩‍💻word"];
	$words = array_values(array_filter($raw, "is_string"));
	file_put_contents($path, yaml_emit(["profanity" => "custom", "banned-words" => $raw]));
	$config = new Config($path, Config::YAML);
	$loader = (new ReflectionClass(DisplayTestLoader::class))->newInstanceWithoutConstructor();
	$loader->words = $config;
	Loader::setInstance($loader);
	$gui = new DisplayTestGui($loader);
	$player = new DisplayTestPlayer();
	$gui->onRun($player, "ui", []);
	$player->lastForm->handleResponse($player, 0);
	$list = $player->lastForm;
	$data = $payload($list);
	$check(count($data["buttons"]) === count($words) + 1, "Word buttons and return button must retain their indices");
	foreach ($words as $index => $word) {
		$text = $data["buttons"][$index]["text"];
		$check(TextFormat::clean($text) === $word, "The visible word must match the config: " . $word);
		$check(!str_contains($text, $word), "The form still sends a contiguous word to the client: " . $word);
		$check(str_starts_with($text, TextFormat::DARK_RED), "List words must retain their original color");
	}
	$check($config->get("banned-words") === $raw, "Rendering the list must not modify stored words");
	$check(str_contains($data["buttons"][3]["text"], "e\u{0301}"), "A combining accent must remain attached to its letter");
	$check(str_contains($data["buttons"][6]["text"], "👩‍💻"), "An emoji sequence must remain intact");
	$check($data["buttons"][count($words)]["text"] === "ui-pf-manage-button-return", "Navigation text must stay unchanged");

	$list->handleResponse($player, 1);
	$actions = $player->lastForm;
	$actionData = $payload($actions);
	$check(TextFormat::clean($actionData["content"]) === "Manage: motherfucker", "The action form must show the selected original word");
	$check(!str_contains($actionData["content"], "fucker"), "A censored substring must also be separated in the action form");
	$check(str_starts_with($actionData["content"], TextFormat::RED), "The action form must retain its original color");
	$actions->handleResponse($player, 0);
	$remaining = array_values(array_diff($raw, ["motherfucker"]));
	$check($config->get("banned-words") === $remaining, "Remove must use the original config value, not the formatted label");
	$check((new Config($path, Config::YAML))->get("banned-words") === $remaining, "Formatted labels must never be persisted to disk");
	$check(count($payload($player->lastForm)["buttons"]) === count($words), "The list must refresh after removing a word");

	$player->lastForm->handleResponse($player, count($words) - 1);
	$check(count($payload($player->lastForm)["buttons"]) === 5, "Return must navigate to the main menu");
	$player->lastForm->handleResponse($player, 0);
	$player->lastForm->handleResponse($player, null);
	$check(count($payload($player->lastForm)["buttons"]) === 5, "Closing the list must navigate to the main menu");
	$player->lastForm->handleResponse($player, 2);
	$player->lastForm->handleResponse($player, [null, "new+word"]);
	$remainingWords = array_values(array_filter($remaining, "is_string"));
	$check($config->get("banned-words") === [...$remainingWords, "new+word"], "Add must persist the unformatted input");
	$player->lastForm->handleResponse($player, 0);
	$check(TextFormat::clean($payload($player->lastForm)["buttons"][count($words) - 1]["text"]) === "new+word", "New words must appear in the management list");

	$config->set("banned-words", []);
	$gui->onRun($player, "ui", []);
	$player->lastForm->handleResponse($player, 0);
	$check(count($payload($player->lastForm)["buttons"]) === 1, "An empty list must retain its return button");
	$player->lastForm->handleResponse($player, 0);
	$check($payload($player->lastForm)["type"] === "form", "Returning from an empty list must work");
	$check(PluginUtils::formatWordForDisplay("", TextFormat::RED) === "", "An empty word must remain empty");
	$check(TextFormat::clean(PluginUtils::formatWordForDisplay("x", TextFormat::RED)) === "x", "Single-character words must stay legible");
	$styled = TextFormat::BOLD . "bad" . TextFormat::RESET . "word";
	$check(TextFormat::clean(PluginUtils::formatWordForDisplay($styled, TextFormat::RED)) === "badword", "Existing formatting codes must not turn into visible letters");
	$check(TextFormat::clean(PluginUtils::formatWordForDisplay("$1\\/word\nnext", TextFormat::RED)) === "$1\\/word\nnext", "Literal punctuation and line breaks must be preserved");
	$check(ProfanityFilterService::containsProfanity("a fuck message", ["fuck"]), "Server-side detection must remain enabled");
	$check(ProfanityFilterService::maskProfanity("a fuck message", ["fuck"]) === "a #### message", "Server-side masking must continue to use raw words");
	echo "PASS: ", $checks, " UI word-display checks", PHP_EOL;
} finally {
	Loader::reset();
	unlink($path);
}
