<?php

declare(strict_types=1);

namespace pocketmine\errorhandler;

final class ErrorRecord{

	/** @var int */
	private $severity;
	/** @var string */
	private $message;
	/** @var string */
	private $file;
	/** @var int */
	private $line;

	public function __construct(int $severity, string $message, string $file, int $line){
		$this->severity = $severity;
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
	}

	public function getSeverity() : int{
		return $this->severity;
	}

	public function getMessage() : string{
		return $this->message;
	}

	public function getFile() : string{
		return $this->file;
	}

	public function getLine() : int{
		return $this->line;
	}
}
