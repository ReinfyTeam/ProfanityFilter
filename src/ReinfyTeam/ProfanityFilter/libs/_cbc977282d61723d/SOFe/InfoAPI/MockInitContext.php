<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\InfoAPI;

use Closure;
use Generator;
use pocketmine\event\Event;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\AwaitGenerator\GeneratorUtil;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\AwaitGenerator\Traverser;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\PmEvent\Blocks;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\PmEvent\Events;
use ReinfyTeam\ProfanityFilter\libs\_cbc977282d61723d\SOFe\Zleep\Zleep;















































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