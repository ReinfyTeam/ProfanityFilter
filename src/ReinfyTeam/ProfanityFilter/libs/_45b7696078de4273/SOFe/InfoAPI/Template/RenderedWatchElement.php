<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_45b7696078de4273\SOFe\InfoAPI\Template;

use Closure;
use Generator;
use RuntimeException;
use ReinfyTeam\ProfanityFilter\libs\_45b7696078de4273\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_45b7696078de4273\SOFe\AwaitGenerator\Traverser;

use function count;
use function implode;
use function is_string;























































































































interface RenderedWatchElement extends RenderedElement {
	/**
	 * @return Traverser<string>
	 */
	public function watch() : Traverser;
}