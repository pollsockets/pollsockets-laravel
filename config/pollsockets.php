<?php

use Pollsockets\Drivers\RedisChannel;

return [
    'driver' => RedisChannel::class,

    // Redis connection name. null uses the default connection.
    'connection' => null,

    // Events older than this many seconds are pruned on the next publish. Must be at least 1.
    'retention' => 60,

    // Request header that carries the publishing client's id.
    'client_header' => 'X-Pollsockets-Client',

    'route' => [
        'prefix' => '_pollsockets',

        // e.g. ['web', 'auth'] if your event names are sensitive.
        'middleware' => [],
    ],
];
