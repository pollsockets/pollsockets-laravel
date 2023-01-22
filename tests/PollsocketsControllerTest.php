<?php

use Mockery\MockInterface;
use Pollsockets\PollsocketsChannel;

it('can poll pollsockets routes', function () {
    $fakeChannel = $this->mock(PollsocketsChannel::class, fn (MockInterface $mock) => $mock
        ->shouldReceive('poll')
        ->once()
        ->andReturn(['test' => 1])
    );

    $this->app->bind(PollsocketsChannel::class, fn () => $fakeChannel);

    $this->get('/_pollsockets/test-channel/poll')
        ->assertOk()
        ->assertJson(['test' => 1]);
});
