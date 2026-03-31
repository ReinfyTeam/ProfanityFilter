<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_f4fbcdfc4cce84bc\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use ReinfyTeam\ProfanityFilter\libs\_f4fbcdfc4cce84bc\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_f4fbcdfc4cce84bc\SOFe\AwaitGenerator\Traverser;

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