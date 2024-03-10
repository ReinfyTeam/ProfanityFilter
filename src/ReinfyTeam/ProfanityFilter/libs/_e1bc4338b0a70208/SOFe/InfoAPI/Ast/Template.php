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

/** The entire template string. */
final class Template {
	public function __construct(
		/** @var (RawText|Expr)[] */
		public array $elements,
	) {
	}
}