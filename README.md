# Serverside implementation of pollsockets tailored for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pollsockets/pollsockets-laravel.svg?style=flat-square)](https://packagist.org/packages/pollsockets/pollsockets-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pollsockets/pollsockets-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pollsockets/pollsockets-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pollsockets/pollsockets-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pollsockets/pollsockets-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pollsockets/pollsockets-laravel.svg?style=flat-square)](https://packagist.org/packages/pollsockets/pollsockets-laravel)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require pollsockets/pollsockets-laravel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="pollsockets-laravel-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="pollsockets-laravel-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="pollsockets-laravel-views"
```

## Usage

```php
$pollsockets = new Pollsockets\Pollsockets();
echo $pollsockets->echoPhrase('Hello, Pollsockets!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vytautas Smilingis](https://github.com/Plytas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
