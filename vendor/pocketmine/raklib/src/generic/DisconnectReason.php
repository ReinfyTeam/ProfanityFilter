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

final class DisconnectReason{
	public const CLIENT_DISCONNECT = 0;
	public const SERVER_DISCONNECT = 1;
	public const PEER_TIMEOUT = 2;
	public const CLIENT_RECONNECT = 3;
	public const SERVER_SHUTDOWN = 4; //TODO: do we really need a separate reason for this in addition to SERVER_DISCONNECT?
	public const SPLIT_PACKET_TOO_LARGE = 5;
	public const SPLIT_PACKET_TOO_MANY_CONCURRENT = 6;
	public const SPLIT_PACKET_INVALID_PART_INDEX = 7;
	public const SPLIT_PACKET_INCONSISTENT_HEADER = 8;

	public static function toString(int $reason) : string{
		return match($reason){
			self::CLIENT_DISCONNECT => "client disconnect",
			self::SERVER_DISCONNECT => "server disconnect",
			self::PEER_TIMEOUT => "timeout",
			self::CLIENT_RECONNECT => "new session established on same address and port",
			self::SERVER_SHUTDOWN => "server shutdown",
			self::SPLIT_PACKET_TOO_LARGE => "received packet split into more parts than allowed",
			self::SPLIT_PACKET_TOO_MANY_CONCURRENT => "too many received split packets being reassembled at once",
			self::SPLIT_PACKET_INVALID_PART_INDEX => "invalid split packet part index",
			self::SPLIT_PACKET_INCONSISTENT_HEADER => "received split packet header inconsistent with previous fragments",
			default => "Unknown reason $reason"
		};
	}
}
