<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_bc4f5edf57a27244\SOFe\InfoAPI\Template;

use Closure;
use RuntimeException;
use function is_string;






















/**
 * @implements EvalChain<RenderedGetElement>
 */
final class GetEvalChain implements EvalChain {
	private mixed $state = null;

	public function then(Closure $map, ?Closure $subscribe) : void {
		$this->state = $map($this->state);
	}

	public function breakOnNonNull() : bool {
		return $this->state !== null;
	}

	public function getResultAsElement() : RenderedElement {
		if (!is_string($this->state)) {
			throw new RuntimeException("Last mapper must return string");
		}
		return new StaticRenderedElement($this->state);
	}
}