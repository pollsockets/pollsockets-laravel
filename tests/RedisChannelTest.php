<?php

use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Redis;
use Mockery\MockInterface;
use Pollsockets\Drivers\RedisChannel;

const NOW_MS = 1791288000124;

beforeEach(function () {
    Date::setTestNow(Date::createFromTimestampMs(NOW_MS));

    $this->redis = Mockery::mock(Connection::class);
    Redis::shouldReceive('connection')->with(null)->andReturn($this->redis)->byDefault();
});

function serveRequest(array $headers): void
{
    $request = Request::create('/', 'POST');
    $request->headers->add($headers);

    app()->instance('request', $request);
}

function expectPublish(MockInterface $redis, string $member, int $score = NOW_MS, int $pruneBefore = NOW_MS - 60000): void
{
    $redis->shouldReceive('zadd')->once()->with('pollsockets.test-channel', $score, $member);
    $redis->shouldReceive('zremrangebyscore')->once()->with('pollsockets.test-channel', '-inf', '('.$pruneBefore);
}

it('can build redis key', function () {
    $method = new ReflectionMethod(RedisChannel::class, 'redisKey');

    expect($method->invoke(new RedisChannel('test-channel')))->toBe('pollsockets.test-channel');
});

it('publishes with the current time in milliseconds and prunes old events', function () {
    expectPublish($this->redis, 'coupons|');

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('stores the origin from a valid client header', function () {
    serveRequest(['X-Pollsockets-Client' => 'abc']);
    expectPublish($this->redis, 'coupons|abc');

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('reads the origin from the configured client header', function () {
    config()->set('pollsockets.client_header', 'X-Tab');
    serveRequest(['X-Tab' => 'k3f9a', 'X-Pollsockets-Client' => 'other']);
    expectPublish($this->redis, 'coupons|k3f9a');

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('ignores an invalid client header', function (string $header) {
    serveRequest(['X-Pollsockets-Client' => $header]);
    expectPublish($this->redis, 'coupons|');

    (new RedisChannel('test-channel'))->publish('coupons');
})->with([
    'space' => 'a b',
    'pipe' => 'a|b',
    'too long' => str_repeat('a', 65),
    'trailing newline' => "abc\n",
]);

it('accepts a 64 character client header', function () {
    $origin = str_repeat('a', 64);
    serveRequest(['X-Pollsockets-Client' => $origin]);
    expectPublish($this->redis, 'coupons|'.$origin);

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('uses an empty origin when no request is bound', function () {
    app()->offsetUnset('request');
    expectPublish($this->redis, 'coupons|');

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('rejects invalid event names', function (string $event) {
    (new RedisChannel('test-channel'))->publish($event);
})->with([
    'empty' => '',
    'pipe' => 'coupons|abc',
])->throws(InvalidArgumentException::class);

it('honours custom retention and connection config', function () {
    config()->set('pollsockets.retention', 120);
    config()->set('pollsockets.connection', 'events');

    $redis = Mockery::mock(Connection::class);
    Redis::shouldReceive('connection')->with('events')->andReturn($redis);
    expectPublish($redis, 'coupons|', pruneBefore: NOW_MS - 120000);

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('accepts retention as a numeric string', function () {
    config()->set('pollsockets.retention', '30');
    expectPublish($this->redis, 'coupons|', pruneBefore: NOW_MS - 30000);

    (new RedisChannel('test-channel'))->publish('coupons');
});

it('rejects an invalid retention', function (mixed $retention) {
    config()->set('pollsockets.retention', $retention);

    (new RedisChannel('test-channel'))->publish('coupons');
})->with([
    'zero' => 0,
    'negative' => -5,
    'not numeric' => 'abc',
    'null' => null,
])->throws(InvalidArgumentException::class, 'retention');

it('polls every event when since is null', function () {
    $this->redis->shouldReceive('zrangebyscore')
        ->once()
        ->with('pollsockets.test-channel', '-inf', '+inf', ['withscores' => true])
        ->andReturn([]);

    expect((new RedisChannel('test-channel'))->poll())->toBe([]);
});

it('polls events strictly after since and casts scores to int', function () {
    $this->redis->shouldReceive('zrangebyscore')
        ->once()
        ->with('pollsockets.test-channel', '(123', '+inf', ['withscores' => true])
        ->andReturn(['coupons|k3f9a' => '1791288000124', 'shopping_lists|' => 1791288000300.0]);

    expect((new RedisChannel('test-channel'))->poll(123))
        ->toBe(['coupons|k3f9a' => 1791288000124, 'shopping_lists|' => 1791288000300]);
});
