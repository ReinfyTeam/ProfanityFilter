<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_5e1b5d7e3ee44176\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use ReinfyTeam\ProfanityFilter\libs\_5e1b5d7e3ee44176\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_5e1b5d7e3ee44176\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;

/**
 * @implements GetOrWatch<RenderedWatchElement, RenderedWatchGroup>
 */
final class Watch implements GetOrWatch {
	public function buildResult(array $elements) : RenderedGroup {
		return new RenderedWatchGroup($elements);
	}

	public function startEvalChain() : EvalChain {
		return new WatchEvalChain;
	}

	public function staticElement(string $raw) : RenderedElement {
		return new StaticRenderedElement($raw);
	}
}