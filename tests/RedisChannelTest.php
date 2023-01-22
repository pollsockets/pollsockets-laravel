<?php

use Pollsockets\Drivers\RedisChannel;
use Illuminate\Support\Facades\Redis;

it('can build redis key', function () {
    $channel = new RedisChannel('test-channel');

    //use reflection to call protected method redisKey()
    $method = new ReflectionMethod(RedisChannel::class, 'redisKey');

    expect($method->invoke($channel))->toBe('pollsockets.test-channel');
});

it('can publish and poll redis channel', function () {
    Redis::shouldReceive('zadd')
        ->with('pollsockets.test-channel', time() + 5, 'test-message')
        ->once();

    Redis::shouldReceive('zadd')
        ->with('pollsockets.test-channel', time() + 5, 'test-message2')
        ->once();

    Redis::shouldReceive('zrangebyscore')
        ->with('pollsockets.test-channel', time(), '+inf', ['withscores' => true])
        ->once()
        ->andReturn(['test-message' => time() + 5, 'test-message2' => time() + 5]);

    $channel = new RedisChannel('test-channel');

    $channel->publish('test-message');
    $channel->publish('test-message2');

    expect($channel->poll())->toBe(['test-message' => time() + 5, 'test-message2' => time() + 5]);
});



