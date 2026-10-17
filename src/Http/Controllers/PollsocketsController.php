<?php

namespace Pollsockets\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pollsockets\Pollsockets;

class PollsocketsController
{
    public function poll(Request $request, string $channel): JsonResponse
    {
        $since = filter_var($request->query('since'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        // Read the time before the poll.
        $time = Pollsockets::now();

        return response()
            ->json([
                'time' => $time,
                'events' => (object) Pollsockets::channel($channel)->poll($since === false ? null : $since),
            ])
            ->header('Cache-Control', 'no-store, private');
    }
}
