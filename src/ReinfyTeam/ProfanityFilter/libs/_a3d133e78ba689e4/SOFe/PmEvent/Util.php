<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\PmEvent;

use Closure;
use ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\AwaitGenerator\Await;
use ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\AwaitGenerator\Channel;
use ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\AwaitGenerator\Traverser;

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