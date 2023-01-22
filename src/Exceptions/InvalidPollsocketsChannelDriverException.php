<?php

namespace Pollsockets\Exceptions;

use Exception;
use Pollsockets\PollsocketsChannel;

class InvalidPollsocketsChannelDriverException extends Exception
{
    const MESSAGE_INVALID_DRIVER = 'Specified pollsockets channel driver must be a class that extends '.PollsocketsChannel::class;

    const MESSAGE_DRIVER_NOT_A_CLASS_STRING = 'Specified pollsockets channel driver must be a class string that extends '.PollsocketsChannel::class;

    public static function invalidDriver(): self
    {
        return new self(message: self::MESSAGE_INVALID_DRIVER);
    }

    public static function notAString(): self
    {
        return new self(message: self::MESSAGE_DRIVER_NOT_A_CLASS_STRING);
    }
}
