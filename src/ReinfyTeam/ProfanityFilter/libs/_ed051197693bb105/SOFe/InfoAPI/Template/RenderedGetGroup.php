<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_ed051197693bb105\SOFe\InfoAPI\Template;

use Closure;
use RuntimeException;
use function is_string;
















































final class RenderedGetGroup implements RenderedGroup {
	/**
	 * @param RenderedGetElement[] $elements
	 */
	public function __construct(private array $elements) {
	}

	public function get() : string {
		$output = "";
		foreach ($this->elements as $element) {
			$output .= $element->get();
		}
		return $output;
	}
}