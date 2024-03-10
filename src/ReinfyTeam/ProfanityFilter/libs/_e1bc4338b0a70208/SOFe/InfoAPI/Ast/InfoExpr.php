<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_e1bc4338b0a70208\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use ReinfyTeam\ProfanityFilter\libs\_e1bc4338b0a70208\SOFe\InfoAPI\QualifiedRef;
use ReinfyTeam\ProfanityFilter\libs\_e1bc4338b0a70208\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;
































/** An expression that resolves info. */
final class InfoExpr {
	public function __construct(
		/** The parent part, e.g. `a b` in `{{ a b c }}`. */
		public ?InfoExpr $parent,

		/** The part that calls a mapping on the parent, e.g. `c(...)` in `{{ a b c(...) }}`. */
		public MappingCall $call,
	) {
	}
}