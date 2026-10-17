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

- PHP 8.2 or higher
- Laravel 11.0 or higher
- Redis 6.0 or higher
- [pollsockets-js](https://github.com/pollsockets/pollsockets-js) 2.x on the client

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

    // Redis connection name. null uses the default connection.
    'connection' => null,

    // Events older than this many seconds are pruned on the next publish. Must be at least 1.
    'retention' => 60,

    // Request header that carries the publishing client's id.
    'client_header' => 'X-Pollsockets-Client',

    'route' => [
        'prefix' => '_pollsockets',

        // e.g. ['web', 'auth'] if your event names are sensitive.
        'middleware' => [],
    ],
];
```

## Usage

### Publishing events

```php
use Pollsockets\Pollsockets;

// A channel separates events from different sources, e.g. one channel per team.
// Channel names may contain letters, digits, "_", ".", ":" and "-".
Pollsockets::channel('team.1')->publish('coupons');
```

An event name is any non-empty string without `|`. Each event is stored in a Redis sorted set `pollsockets.{channel}`,
scored with the publish time in Unix milliseconds. Publishing the same event from the same origin again only moves
its time forward, and the client fires each event name once per poll, not once per publish.

Events older than `retention` seconds are removed on the next publish to the channel.

Publish after the database transaction commits, for example inside `DB::afterCommit(...)` or after the transaction
block. Otherwise a client can receive the event and reload before the new data is visible.

```php
DB::transaction(function () {
    // ... write the coupons
    DB::afterCommit(fn () => Pollsockets::channel('team.1')->publish('coupons'));
});
```

### Polling

The client polls `GET /_pollsockets/{channel}/poll?since=<ms>` and gets:

```json
{
    "time": 1791288000500,
    "events": {
        "coupons|k3f9a": 1791288000124,
        "shopping_lists|": 1791288000300
    }
}
```

- `time` is the server time in Unix milliseconds when the request was handled.
- `events` holds every event published after `since` (exclusive), as `"{event}|{origin}": <publish time ms>`.
  Without a valid `since`, it holds every stored event. It is always a JSON object.
- Expired events stay in Redis until the next publish to the channel prunes them, so a response can contain events
  older than `retention`.
- The response is sent with `Cache-Control: no-store, private`.

The client keeps track of the cursor, skips its own events and detects when it was away for longer than the retention
window. pollsockets-js 2.x does all of this for you.

### Delivery and limitations

An event's score is the time read just before its ZADD, so events are not always stored in score order. A poll can
return a newer event while an older-stamped event is not stored yet, and the cursor then moves past it. To cover
this, the JS client re-reads the last 2 seconds on every poll and skips the events it already handled. A publish
whose ZADD lands up to about 2 seconds after its timestamp is still delivered. A longer delay, for example a slow
Redis connection, can cause the event to be missed.

### Initial cursor

To make the client receive every change made after the page loaded its data, pass `Pollsockets::now()` to it as
`since`. Capture the time before you load the data that the page shows, for example on the first line of the
controller. A change published between the data query and a later timestamp would otherwise be missed.

```php
public function index()
{
    $since = Pollsockets::now(); // before the queries

    return view('coupons.index', [
        'coupons' => Coupon::all(),
        'pollsocketsSince' => $since,
    ]);
}
```

```blade
<script>
    window.pollsocketsSince = {{ $pollsocketsSince }};
</script>
```

With Inertia, put the timestamp in a prop that is resolved before the other props.

The client re-reads the 2 seconds before `since`, so right after start it can fire one extra notification for a
change that the page already shows.

Without it, the client uses the time of its first poll response as the starting point.

### Skipping the publishing tab (origin)

The browser tab that caused a change usually does not need to be notified about it. Send the client id of
pollsockets-js in the `X-Pollsockets-Client` header (configurable with `client_header`) on your app's own write
requests, for example with an HTTP interceptor. The id is stored as the event's origin, and that client skips the event.

The origin must match `^[A-Za-z0-9_-]{1,64}$`. Any other value, and publishes without an HTTP request
(queued jobs, console commands), store an empty origin, so every client receives the event.

### Securing the poll route

Anyone who can reach the poll route can see the event names of a channel. The events carry no payload, but if the
names or channel names are sensitive, protect the route with `route.middleware`, for example `['web', 'auth']`.
Note that polling through `web` starts and writes the session on every poll (about once per second per tab), and
keeps the session alive while the tab is open.

### Keeping the client in sync with the config

- If you change `retention`, set the pollsockets-js `retention` option to the same value in milliseconds.
- If you change `route.prefix`, pass the matching `url` option to pollsockets-js.

## Upgrading from v1

- `publish($message, $delta)` is now `publish($event)`. Remove the `$delta` argument. Events no longer expire
  after `$delta` seconds. The next publish to the channel prunes events older than `retention` seconds.
- Event names must not be empty or contain `|`.
- The poll response changed from `{"event": <unix seconds>}` to `{"time": <ms>, "events": {"event|origin": <ms>}}`,
  and the route accepts a `since` query parameter. Upgrade the client to pollsockets-js 2.x at the same time.
- Custom drivers must implement `publish(string $event): void` and `poll(?int $since = null): array`.
  `PollsocketsChannel::member($event)` builds the stored member, including the origin.
- Publish the config file again, or add the new `connection`, `retention`, `client_header` and `route` keys
  to your existing one.

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
