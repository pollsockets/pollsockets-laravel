<?php

namespace Pollsockets\Http\Controllers;

use Pollsockets\Pollsockets;

class PollsocketsController
{
    /**
     * @return array<string, int>
     */
    public function poll(string $channelName): array
    {
        return Pollsockets::channel($channelName)->poll();
    }
}
