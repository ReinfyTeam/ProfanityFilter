<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\SOFe\InfoAPI\Pathfind;

use Closure;
use Shared\SOFe\InfoAPI\Mapping;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\SOFe\InfoAPI\QualifiedRef;
use ReinfyTeam\ProfanityFilter\libs\_a78acd1e7b2b6d62\SOFe\InfoAPI\ReadIndices;
use SplPriorityQueue;
use function array_merge;
use function array_shift;
use function count;






















































































































/**
 * @extends SplPriorityQueue<Cost, Path>
 */
final class Heap extends SplPriorityQueue {
	public function compare(mixed $priority1, mixed $priority2) : int {
		return $priority1->compare($priority2);
	}

	public function insertPath(Path $path) : void {
		$this->insert($path, $path->cost);
	}
}