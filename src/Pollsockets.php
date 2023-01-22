<?php

namespace Pollsockets;

class Pollsockets
{
	public static function channel(string $channelName): PollsocketsChannel
	{
		return resolve(PollsocketsChannel::class, ['channelName' => $channelName]);
	}
}
