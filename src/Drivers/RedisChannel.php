<?php

namespace Pollsockets\Drivers;

use Illuminate\Support\Facades\Redis;
use Pollsockets\PollsocketsChannel;

class RedisChannel extends PollsocketsChannel
{
    /**
     * @inheritDoc
     */
    public function poll(): array
    {
        return Redis::zrangebyscore($this->redisKey(), time(), '+inf', ['withscores' => true]);
    }

    public function publish(string $message): void
    {
        Redis::zadd($this->redisKey(), time() + 5, $message);
    }

    protected function redisKey(): string
    {
        return "pollsockets.{$this->channelName}";
    }
}
