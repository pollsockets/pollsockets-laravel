<?php

namespace Pollsockets;

abstract class PollsocketsChannel
{
    public function __construct(public string $channelName)
    {
    }

    /**
     * @return array<string, int>
     */
    abstract public function poll(): array;

    abstract public function publish(string $message): void;
}
