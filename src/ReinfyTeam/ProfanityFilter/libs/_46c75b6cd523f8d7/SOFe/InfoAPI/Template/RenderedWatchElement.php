<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_46c75b6cd523f8d7\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use ReinfyTeam\ProfanityFilter\libs\_46c75b6cd523f8d7\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_46c75b6cd523f8d7\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;























































































































interface RenderedWatchElement extends RenderedElement {
	/**
	 * @return Traverser<string>
	 */
	public function watch() : Traverser;
}