<?php

namespace Pollsockets;

use InvalidArgumentException;

abstract class PollsocketsChannel
{
    public function __construct(public string $channelName) {}

    abstract public function publish(string $event): void;

    /**
     * @return array<string, int> member => score in Unix milliseconds
     */
    abstract public function poll(?int $since = null): array;

    /**
     * The publishing client's id from the current request, or an empty string when there is none.
     */
    protected function origin(): string
    {
        if (! app()->bound('request')) {
            return '';
        }

        $origin = request()->headers->get(config()->string('pollsockets.client_header'));

        if ($origin === null || preg_match('/^[A-Za-z0-9_-]{1,64}$/D', $origin) !== 1) {
            return '';
        }

        return $origin;
    }

    protected function member(string $event): string
    {
        if ($event === '' || str_contains($event, '|')) {
            throw new InvalidArgumentException('Pollsockets event name must be non-empty and must not contain "|".');
        }

        return $event.'|'.$this->origin();
    }
}
