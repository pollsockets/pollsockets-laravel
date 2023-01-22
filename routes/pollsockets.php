<?php

use Illuminate\Support\Facades\Route;
use Pollsockets\Http\Controllers\PollsocketsController;

Route::get('_pollsockets/{channel}/poll', [PollsocketsController::class, 'poll'])->name('pollsockets.poll');
