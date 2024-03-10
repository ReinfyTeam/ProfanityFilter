<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\InfoAPI\Template;

use pocketmine\command\CommandSender;
use Shared\SOFe\InfoAPI\Display;
use ReinfyTeam\ProfanityFilter\libs\_a3d133e78ba689e4\SOFe\AwaitGenerator\Traverser;

use function count;
use function sprintf;



































































































































final class PathOnly implements CoalesceChoice {
	public function __construct(
		public ResolvedPath $path,
	) {
	}

	public function getPath() : ResolvedPath {
		return $this->path;
	}

	public function getDisplay() : ?Display {
		return null;
	}
}