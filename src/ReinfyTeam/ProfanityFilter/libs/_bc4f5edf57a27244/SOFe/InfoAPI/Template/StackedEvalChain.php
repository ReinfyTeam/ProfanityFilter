<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Ast;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Ast\MappingCall;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Pathfind;
use ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\ReadIndices;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function json_decode;
use function range;
use function sprintf;














































































































































































































































































final class StackedEvalChain implements NestedEvalChain {
	public function __construct(private NestedEvalChain $chain) {
		// state: [parentState, isChildBroken, childState]
		$this->chain->then(function($parentState) {
			return [$parentState, false, null];
		}, null);
	}

	public function then(Closure $map, ?Closure $subscribe) : void {
		$this->chain->then(function($state) use ($map) {
			/** @var array{mixed, bool, mixed} $state */
			[$parentState, $isBroken, $myState] = $state;
			if ($isBroken) {
				return;
			}
			$myState = $map($myState);
			return [$parentState, $isBroken, $myState];
		}, function($state) use ($subscribe) {
			/** @var array{mixed, bool, mixed} $state */
			[$_parentState, $isBroken, $myState] = $state;
			return ($isBroken || $subscribe === null) ? null : $subscribe($myState);
		} );
	}

	public function breakOnNonNull() : bool {
		$this->chain->then(function($state) {
			/** @var array{mixed, bool, mixed} $state */
			[$parentState, $isBroken, $myState] = $state;
			$isBroken = $isBroken || $myState !== null;
			return [$parentState, $isBroken, $myState];
		}, null);
		return false;
	}

	/**
	 * Complete this stack. Merge the stacked result into the original value.
	 */
	public function finish(Closure $merge) : void {
		$this->chain->then(function($state) use ($merge) {
			/** @var array{mixed, bool, mixed} $state */
			[$parentState, $_isBroken, $myState] = $state;
			return $merge($parentState, $myState);
		}, null);
	}
}