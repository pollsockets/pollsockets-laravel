<?php

namespace Pollsockets\Tests\Fakes;

use Pollsockets\PollsocketsChannel;

class FakeChannel extends PollsocketsChannel
{
    public function publish(string $event): void {}

    /**
     * {@inheritDoc}
     */
    public function poll(?int $since = null): array
    {
        return [];
    }
}
