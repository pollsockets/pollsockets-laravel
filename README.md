# Serverside implementation of pollsockets tailored for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pollsockets/pollsockets-laravel.svg?style=flat-square)](https://packagist.org/packages/pollsockets/pollsockets-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pollsockets/pollsockets-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pollsockets/pollsockets-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pollsockets/pollsockets-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pollsockets/pollsockets-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pollsockets/pollsockets-laravel.svg?style=flat-square)](https://packagist.org/packages/pollsockets/pollsockets-laravel)

## Why pollsockets?

Pollsockets is a simple, yet powerful, server-client communication library. It is designed to be used in situations where you need to send data from the server to the client. It is a great alternative to websockets, as it is much simpler to implement and use.

## How does it work?

Pollsockets uses a simple polling mechanism to send events from the server to the client. Instead of polling an endpoint that returns data, pollsockets polls an endpoint that returns a list of events that have occurred, thus saving bandwidth and response time. The client then processes the events and updates the UI accordingly.

## Ecosystem

### Server side
- [Laravel](https://github.com/pollsockets/pollsockets-laravel) 

### Client side
- [Javascript](https://github.com/pollsockets/pollsockets-js)

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher
- Redis 6.0 or higher

## Installation

You can install the package via composer:

```bash
composer require pollsockets/pollsockets-laravel
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="pollsockets-config"
```

This is the contents of the published config file:

```php
use Pollsockets\Drivers\RedisChannel;

return [
    'driver' => RedisChannel::class,
];
```

## Usage

```php
use Pollsockets\Pollsockets;

// Channel name can be any string. Can be used to separate events from different sources.
$channelName = 'channel';
// Event name can be any string. Can be used to separate events of the same type.
$event = 'reload';

// Publish an event to the channel from anywhere in your code. Perfect for informing client about changes in the database.
Pollsockets::channel($channelName)->publish($event);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vytautas Smilingis](https://github.com/Plytas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
