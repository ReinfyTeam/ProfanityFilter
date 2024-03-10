<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI\Template;

use Closure;
use pocketmine\command\CommandSender;
use RuntimeException;
use Shared\SOFe\InfoAPI\Mapping;
use Shared\SOFe\InfoAPI\Parameter;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI\Ast;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI\Ast\MappingCall;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI\Pathfind;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI\ReadIndices;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function json_decode;
use function range;
use function sprintf;




























































































































































































































/**
 * @template R of RenderedElement
 * @template G of RenderedGroup
 */
interface GetOrWatch {
	/**
	 * @param R[] $elements
	 * @return G
	 */
	public function buildResult(array $elements) : RenderedGroup;

	/**
	 * @return EvalChain<R>
	 */
	public function startEvalChain() : EvalChain;

	/**
	 * @return R
	 */
	public function staticElement(string $raw) : RenderedElement;
}