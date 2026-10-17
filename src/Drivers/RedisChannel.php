<?php

namespace Pollsockets\Drivers;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Pollsockets\Pollsockets;
use Pollsockets\PollsocketsChannel;

class RedisChannel extends PollsocketsChannel
{
    public function publish(string $event): void
    {
        $connection = $this->connection();
        $member = $this->member($event);
        $retentionMs = $this->retention() * 1000;

        // Read the time last. phpredis connects when the connection is resolved, so its connect time falls before
        // the timestamp. Predis connects on the first command, so its connect time can still fall between the
        // timestamp and the ZADD; the client's 2 second overlap covers that.
        $now = Pollsockets::now();

        $connection->zadd($this->redisKey(), $now, $member);
        $connection->zremrangebyscore($this->redisKey(), '-inf', '('.($now - $retentionMs));
    }

    /**
     * {@inheritDoc}
     */
    public function poll(?int $since = null): array
    {
        $members = $this->connection()->zrangebyscore(
            $this->redisKey(),
            $since === null ? '-inf' : '('.$since,
            '+inf',
            ['withscores' => true],
        );

        $events = [];

        foreach (is_array($members) ? $members : [] as $member => $score) {
            $events[(string) $member] = (int) (is_numeric($score) ? $score : 0);
        }

        return $events;
    }

    protected function connection(): Connection
    {
        $connection = config('pollsockets.connection');

        return Redis::connection(is_string($connection) ? $connection : null);
    }

    /**
     * Retention in seconds. Accepts an int or a numeric string, e.g. from env().
     */
    protected function retention(): int
    {
        $retention = config('pollsockets.retention');

        if (! is_numeric($retention) || (int) $retention < 1) {
            throw new InvalidArgumentException('Pollsockets retention must be a number of seconds of at least 1.');
        }

        return (int) $retention;
    }

    protected function redisKey(): string
    {
        return "pollsockets.{$this->channelName}";
    }
}
