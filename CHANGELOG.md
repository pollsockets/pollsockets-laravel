# Changelog

All notable changes to `pollsockets-laravel` will be documented in this file.

## 2.0.0 - Unreleased

### Breaking changes

- `PollsocketsChannel::publish(string $message, int $delta = 5)` is now `publish(string $event)`. The `$delta` argument is removed.
- `PollsocketsChannel::poll()` is now `poll(?int $since = null)`, and returns members with scores in Unix milliseconds.
- The poll response is now `{"time": <ms>, "events": {"<event>|<origin>": <ms>}}`. `events` is always a JSON object. Use pollsockets-js 2.x.
- Event names must be non-empty and must not contain `|`.
- Channel names must match `[A-Za-z0-9_.:-]+`, in the poll route and in `Pollsockets::channel()`.

### Added

- `since` query parameter on the poll route, with an exclusive lower bound.
- Origin: the `X-Pollsockets-Client` request header is stored with each event, so the publishing client can skip it.
- Config keys `connection`, `retention`, `client_header`, `route.prefix` and `route.middleware`.
- Events older than `retention` seconds are pruned on publish.
- `Pollsockets::now()` returns the server time in Unix milliseconds.
- The poll response is sent with `Cache-Control: no-store, private`.

### Removed

- The `Pollsockets` alias for a facade class that did not exist, and the autoload entry for the missing `database/factories` directory.

## 1.0.0 - 2023-03-31

### First release

**Full Changelog**: https://github.com/pollsockets/pollsockets-laravel/commits/1.0.0
