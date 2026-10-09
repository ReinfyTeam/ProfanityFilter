<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\event\Event;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\AwaitGenerator\GeneratorUtil;
use ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\PmEvent\Blocks;
use ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\PmEvent\Events;
use ReinfyTeam\ProfanityFilter\libs\_3c77a84f3f28197f\SOFe\Zleep\Zleep;

interface InitContext {
	/**
	 * @template E of Event
	 * @param class-string<E>[] $events
	 * @param Closure(E): string $interpreter
	 * @return Traverser<E>
	 */
	public function watchEvent(array $events, string $key, Closure $interpreter) : Traverser;

	/**
	 * @return Traverser<null>
	 */
	public function watchBlock(Position $position) : Traverser;

	/**
	 * @return Generator<mixed, mixed, mixed, void>
	 */
	public function sleep(int $ticks) : Generator;
}