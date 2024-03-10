<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\InfoAPI\Ast;
use ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\InfoAPI\Ast\MappingCall;
use ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\InfoAPI\Pathfind;
use ReinfyTeam\ProfanityFilter\libs\_c09f573043d35f0e\SOFe\InfoAPI\ReadIndices;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function json_decode;
use function range;
use function sprintf;


















































































































































































































































interface NestedEvalChain {
	/**
	 * Add a step in the chain to map the return value of the previous step.
	 * The first step receives null.
	 *
	 * @param Closure(mixed): mixed $map
	 * @param ?Closure(mixed): ?Traverser<null> $subscribe
	 */
	public function then(Closure $map, ?Closure $subscribe) : void;

	/**
	 * Returns true if the inference is non-watching and the last step returned non-null.
	 */
	public function breakOnNonNull() : bool;
}