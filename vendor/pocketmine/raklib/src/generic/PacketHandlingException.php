<?php

/*
 * This file is part of RakLib.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/RakLib>
 *
 * RakLib is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * RakLib is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace raklib\generic;

class PacketHandlingException extends \RuntimeException{

	/** @phpstan-var DisconnectReason::* */
	private int $disconnectReason;

	/**
	 * @phpstan-param DisconnectReason::* $disconnectReason
	 */
	public function __construct(string $message, int $disconnectReason, int $code = 0, ?\Throwable $previous = null){
		$this->disconnectReason = $disconnectReason;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @phpstan-return DisconnectReason::*
	 */
	public function getDisconnectReason() : int{
		return $this->disconnectReason;
	}
}
