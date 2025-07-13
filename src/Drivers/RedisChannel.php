<?php

namespace Pollsockets\Drivers;

use Illuminate\Support\Facades\Redis;
use Pollsockets\PollsocketsChannel;

class RedisChannel extends PollsocketsChannel
{
    /**
     * {@inheritDoc}
     */
    public function poll(): array
    {
        return Redis::zrangebyscore($this->redisKey(), (string) time(), '+inf', ['withscores' => true]); // @phpstan-ignore-line return.type
    }

    public function publish(string $message, int $delta = 5): void
    {
        Redis::zadd($this->redisKey(), time() + $delta, $message);
    }

    protected function redisKey(): string
    {
        return "pollsockets.{$this->channelName}";
    }
}
