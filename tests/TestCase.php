<?php

namespace Pollsockets\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pollsockets\PollsocketsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PollsocketsServiceProvider::class,
        ];
    }
}
