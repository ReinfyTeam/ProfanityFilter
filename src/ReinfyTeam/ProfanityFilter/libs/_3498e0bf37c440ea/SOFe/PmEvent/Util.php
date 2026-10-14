<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_3498e0bf37c440ea\SOFe\PmEvent;

use Closure;
use ReinfyTeam\ProfanityFilter\libs\_3498e0bf37c440ea\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_3498e0bf37c440ea\SOFe\AwaitGenerator\Channel;
use ReinfyTeam\ProfanityFilter\libs\_3498e0bf37c440ea\SOFe\AwaitGenerator\Traverser;

final class Util {
	/**
	 * @template T
	 * @param Channel<T>[] $channels
	 * @param ?Closure(): void $finalize
	 * @return Traverser<T>
	 */
	public static function traverseChannels(array $channels, ?Closure $finalize = null) : Traverser {
		return Traverser::fromClosure(function() use ($channels, $finalize) {
			try {
				while (true) {
					[, $value] = yield from Await::safeRace(array_map(fn(Channel $channel) => $channel->receive(), $channels));
					yield $value => Traverser::VALUE;
				}
			} finally {
				if($finalize !== null) {
					$finalize();
				}
			}
		});
	}
}