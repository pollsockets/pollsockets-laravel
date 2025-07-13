<?php

namespace Pollsockets\Tests\Fakes;

use Pollsockets\PollsocketsChannel;

class FakeChannel extends PollsocketsChannel
{
    /**
     * {@inheritDoc}
     */
    public function poll(): array
    {
        return [];
    }

    public function publish(string $message, int $delta = 5): void {}
}
