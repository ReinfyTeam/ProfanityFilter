<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\InfoAPI;

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