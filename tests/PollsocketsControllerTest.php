<?php

use Illuminate\Support\Facades\Date;
use Mockery\MockInterface;
use Pollsockets\PollsocketsChannel;
use Pollsockets\Tests\Fakes\AddHeaderMiddleware;

beforeEach(function () {
    Date::setTestNow(Date::createFromTimestampMs(1791288000500));
});

function fakeChannel(?int $since, array $events = []): void
{
    $channel = test()->mock(PollsocketsChannel::class, fn (MockInterface $mock) => $mock
        ->shouldReceive('poll')
        ->once()
        ->with($since)
        ->andReturn($events)
    );

    app()->bind(PollsocketsChannel::class, fn () => $channel);
}

it('returns the server time and events', function () {
    fakeChannel(null, ['coupons|k3f9a' => 1791288000124, 'shopping_lists|' => 1791288000300]);

    $this->get('/_pollsockets/test-channel/poll')
        ->assertOk()
        ->assertExactJson([
            'time' => 1791288000500,
            'events' => ['coupons|k3f9a' => 1791288000124, 'shopping_lists|' => 1791288000300],
        ]);
});

it('returns events as an empty object when there are none', function () {
    fakeChannel(null);

    $content = $this->get('/_pollsockets/test-channel/poll')->assertOk()->getContent();

    expect($content)->toContain('"events":{}');
});

it('passes a valid since to the channel', function () {
    fakeChannel(123);

    $this->get('/_pollsockets/test-channel/poll?since=123')->assertOk();
});

it('ignores an invalid since', function (string $query) {
    fakeChannel(null);

    $this->get('/_pollsockets/test-channel/poll?'.$query)->assertOk();
})->with([
    'not a number' => 'since=abc',
    'negative' => 'since=-1',
    'array' => 'since[]=1',
]);

it('disables caching', function () {
    fakeChannel(null);

    $this->get('/_pollsockets/test-channel/poll')
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('names the route', function () {
    expect(route('pollsockets.poll', ['channel' => 'test-channel'], false))->toBe('/_pollsockets/test-channel/poll');
});

it('accepts channel names with dots, colons and dashes', function () {
    fakeChannel(null);

    $this->get('/_pollsockets/team.1:user_2-a/poll')->assertOk();
});

it('returns 404 for an invalid channel name', function () {
    $this->get('/_pollsockets/bad%20channel/poll')->assertNotFound();
    $this->get('/_pollsockets/bad%7Cchannel/poll')->assertNotFound();
});

it('falls back to the default prefix when only route middleware is configured', function () {
    config()->set('pollsockets.route', ['middleware' => [AddHeaderMiddleware::class]]);

    require __DIR__.'/../routes/pollsockets.php';

    fakeChannel(null);

    $this->get('/_pollsockets/test-channel/poll')
        ->assertOk()
        ->assertHeader('X-Test-Middleware', 'applied');
});

it('applies the configured prefix and middleware', function () {
    config()->set('pollsockets.route.prefix', '/custom/');
    config()->set('pollsockets.route.middleware', [AddHeaderMiddleware::class]);

    // Routes are registered when the provider boots, so load the route file again with the new config.
    require __DIR__.'/../routes/pollsockets.php';

    fakeChannel(null);

    $this->get('/custom/test-channel/poll')
        ->assertOk()
        ->assertHeader('X-Test-Middleware', 'applied');
});
