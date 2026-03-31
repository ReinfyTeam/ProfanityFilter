<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_b8923f35e54afff0\SOFe\InfoAPI;

use Shared\SOFe\InfoAPI\Mapping;

use function array_filter;
use function array_unshift;
use function count;



























































final class ScoredMapping {
	public function __construct(
		public int $score,
		public Mapping $mapping,
	) {
	}
}