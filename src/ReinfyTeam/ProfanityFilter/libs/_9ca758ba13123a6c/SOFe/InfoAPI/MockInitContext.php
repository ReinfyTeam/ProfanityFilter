<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\event\Event;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\AwaitGenerator\GeneratorUtil;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\PmEvent\Blocks;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\PmEvent\Events;
use ReinfyTeam\ProfanityFilter\libs\_9ca758ba13123a6c\SOFe\Zleep\Zleep;















































final class MockInitContext implements InitContext {
	public function watchEvent(array $events, string $key, Closure $interpreter) : Traverser {
		return new Traverser(GeneratorUtil::empty());
	}

	public function watchBlock(Position $position) : Traverser {
		return new Traverser(GeneratorUtil::empty());
	}

	public function sleep(int $ticks) : Generator {
		return GeneratorUtil::pending();
	}
}