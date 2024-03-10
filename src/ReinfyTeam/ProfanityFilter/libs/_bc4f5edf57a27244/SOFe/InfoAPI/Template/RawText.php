<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Template;

use pocketmine\command\CommandSender;
use Shared\SOFe\InfoAPI\Display;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\AwaitGenerator\Traverser;

use function count;
use function sprintf;











final class RawText implements TemplateElement {
	public function __construct(public string $raw) {
	}

	public function render(mixed $context, ?CommandSender $sender, GetOrWatch $getOrWatch) : RenderedElement {
		return $getOrWatch->staticElement($this->raw);
	}
}