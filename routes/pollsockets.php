<?php

use Illuminate\Support\Facades\Route;
use Pollsockets\Http\Controllers\PollsocketsController;
use Pollsockets\Pollsockets;

Route::prefix(trim(config()->string('pollsockets.route.prefix', '_pollsockets'), '/'))
    ->middleware(config()->array('pollsockets.route.middleware', []))
    ->group(function () {
        Route::get('{channel}/poll', [PollsocketsController::class, 'poll'])
            ->name('pollsockets.poll')
            ->where('channel', Pollsockets::CHANNEL_PATTERN);
    });
