<?php

namespace Pollsockets;

use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class Pollsockets
{
    /**
     * Valid channel name, shared by the poll route and channel().
     */
    public const CHANNEL_PATTERN = '[A-Za-z0-9_.:-]+';

    public static function channel(string $channelName): PollsocketsChannel
    {
        if (preg_match('/^'.self::CHANNEL_PATTERN.'$/D', $channelName) !== 1) {
            throw new InvalidArgumentException('Pollsockets channel name must match '.self::CHANNEL_PATTERN.'.');
        }

        return resolve(PollsocketsChannel::class, ['channelName' => $channelName]);
    }

    /**
     * Current server time in Unix milliseconds, usable as the client's initial `since`.
     */
    public static function now(): int
    {
        return Date::now()->getTimestampMs();
    }
}
