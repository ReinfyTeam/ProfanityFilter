<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\QualifiedRef;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;























































/** An argument passed to a mapping. */
final class Arg {
	public function __construct(
		/** Name of the argument if specified, e.g. `d` in `{ a:b(c, d=e) }`. */
		public ?string $name,

		/**
		 * The value of the argument.
		 * Parses as an Expr if it starts with an identifier (except `true` and `false`),
		 * otherwise parses as one JSON expression. */
		public JsonValue|Expr $value,
	) {
	}
}