<?php

namespace Pollsockets;

use Illuminate\Foundation\Application;
use Pollsockets\Exceptions\InvalidPollsocketsChannelDriverException;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PollsocketsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('pollsockets')
            ->hasConfigFile('pollsockets')
            ->hasRoute('pollsockets')
            ->hasInstallCommand(fn (InstallCommand $command) => $command
                ->publishConfigFile()
                ->askToStarRepoOnGitHub('pollsockets/pollsockets-laravel')
            );
    }

    public function packageBooted(): void
    {
        $this->app->bind(PollsocketsChannel::class, function (Application $app, array $parameters) {
            $driver = config('pollsockets.driver');

            if (! is_string($driver)) {
                throw InvalidPollsocketsChannelDriverException::notAString();
            }

            if (! is_a($driver, PollsocketsChannel::class, true)) {
                throw InvalidPollsocketsChannelDriverException::invalidDriver();
            }

            return new $driver(...$parameters);
        });
    }
}
