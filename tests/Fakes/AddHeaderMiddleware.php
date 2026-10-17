<?php

namespace Pollsockets\Tests\Fakes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddHeaderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Test-Middleware', 'applied');

        return $response;
    }
}
