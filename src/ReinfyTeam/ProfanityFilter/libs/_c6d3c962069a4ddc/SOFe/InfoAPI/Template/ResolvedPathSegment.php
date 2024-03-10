<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Ast;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Ast\MappingCall;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Pathfind;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\ReadIndices;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function json_decode;
use function range;
use function sprintf;





















































































































































































final class ResolvedPathSegment {
	/**
	 * @param list<ResolvedPathArg> $args
	 */
	public function __construct(
		public Mapping $mapping,
		public array $args,
	) {
	}
}