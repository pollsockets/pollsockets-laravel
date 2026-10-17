<?php

use Illuminate\Support\Facades\Date;
use Mockery\MockInterface;
use Pollsockets\Exceptions\InvalidPollsocketsChannelDriverException;
use Pollsockets\Pollsockets;
use Pollsockets\PollsocketsChannel;
use Pollsockets\Tests\Fakes\FakeChannel;

it('can initialise Pollsockets channel', function () {
    $channel = Pollsockets::channel('test-channel');
    $this->assertInstanceOf(PollsocketsChannel::class, $channel);
});

it('can initialise Pollsockets channel with custom driver', function () {
    config()->set('pollsockets.driver', FakeChannel::class);

    $channel = Pollsockets::channel('test-channel');
    $this->assertInstanceOf(FakeChannel::class, $channel);
    $this->assertInstanceOf(PollsocketsChannel::class, $channel);
});

it('throws exception when channel driver is not a string', function () {
    config()->set('pollsockets.driver', 123);

    Pollsockets::channel('test-chanel');
})->throws(InvalidPollsocketsChannelDriverException::class, InvalidPollsocketsChannelDriverException::MESSAGE_DRIVER_NOT_A_CLASS_STRING);

it('throws exception when channel driver is not a valid class', function () {
    config()->set('pollsockets.driver', 'InvalidDriver');

    Pollsockets::channel('test-chanel');
})->throws(InvalidPollsocketsChannelDriverException::class, InvalidPollsocketsChannelDriverException::MESSAGE_INVALID_DRIVER);

it('can poll a channel', function () {
    $fakeChannel = $this->mock(PollsocketsChannel::class, fn (MockInterface $mock) => $mock
        ->shouldReceive('poll')
        ->once()
        ->andReturn(['test' => 1])
    );

    $this->app->bind(PollsocketsChannel::class, fn () => $fakeChannel);

    $channel = Pollsockets::channel('test-channel');
    $this->assertIsArray($channel->poll());
});

it('can publish to a channel', function () {
    $fakeChannel = $this->mock(PollsocketsChannel::class, fn (MockInterface $mock) => $mock
        ->shouldReceive('publish')
        ->once()
        ->with('test-event')
    );

    $this->app->bind(PollsocketsChannel::class, function () use ($fakeChannel) {
        return $fakeChannel;
    });

    $channel = Pollsockets::channel('test-channel');
    $channel->publish('test-event');
});

it('returns the current time in milliseconds', function () {
    Date::setTestNow(Date::createFromTimestampMs(1791288000124));

    expect(Pollsockets::now())->toBe(1791288000124);
});

it('accepts valid channel names', function (string $channelName) {
    expect(Pollsockets::channel($channelName)->channelName)->toBe($channelName);
})->with(['team.1:user_2-a', 'a']);

it('rejects invalid channel names', function (string $channelName) {
    Pollsockets::channel($channelName);
})->with([
    'empty' => '',
    'space' => 'bad channel',
    'pipe' => 'bad|channel',
    'trailing newline' => "channel\n",
])->throws(InvalidArgumentException::class);
