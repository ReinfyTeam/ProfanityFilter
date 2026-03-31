<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\Ast;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\Ast\MappingCall;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\Pathfind;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI\ReadIndices;

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