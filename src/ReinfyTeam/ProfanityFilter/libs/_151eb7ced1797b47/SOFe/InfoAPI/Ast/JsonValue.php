<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_151eb7ced1797b47\SOFe\InfoAPI\Ast;

use JsonException;
use Shared\SOFe\InfoAPI\Mapping;
use ReinfyTeam\ProfanityFilter\libs\_151eb7ced1797b47\SOFe\InfoAPI\QualifiedRef;
use ReinfyTeam\ProfanityFilter\libs\_151eb7ced1797b47\SOFe\InfoAPI\StringParser;
use function is_numeric;
use function is_string;
use function json_decode;
use function strlen;






































































/** A value in JSON format to be interpreted based on the type. */
final class JsonValue {
	public function __construct(
		public string $asString,
		public string $json,
	) {
	}
}