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





















final class PluginInitContext implements InitContext {
	public function __construct(private Plugin $plugin) {
	}

	public function watchEvent(array $events, string $key, Closure $interpreter) : Traverser {
		return Events::watch($this->plugin, $events, $key, $interpreter);
	}

	public function watchBlock(Position $position) : Traverser {
		return Traverser::fromClosure(function() use ($position) {
			$traverser = Blocks::watch($position);
			try {
				while ($traverser->next($_block)) {
					yield null => Traverser::VALUE;
				}
			} finally {
				yield from $traverser->interrupt();
			}
		});
	}

	public function sleep(int $ticks) : Generator {
		return Zleep::sleepTicks($this->plugin, $ticks);
	}
}