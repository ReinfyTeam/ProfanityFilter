<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_e1bc4338b0a70208\SOFe\InfoAPI;

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