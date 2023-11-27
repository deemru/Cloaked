# Cloaked

[![packagist](https://img.shields.io/packagist/v/deemru/cloaked.svg)](https://packagist.org/packages/deemru/cloaked) [![php-v](https://img.shields.io/packagist/php-v/deemru/cloaked.svg)](https://packagist.org/packages/deemru/cloaked)  [![GitHub](https://img.shields.io/github/actions/workflow/status/deemru/Cloaked/php.yml?label=github%20actions)](https://github.com/deemru/Cloaked/actions/workflows/php.yml)  [![codacy](https://img.shields.io/codacy/grade/a66e9c0a9c024bd49135a15073390e65.svg?label=codacy)](https://app.codacy.com/gh/deemru/Cloaked/files) [![license](https://img.shields.io/packagist/l/deemru/cloaked.svg)](https://packagist.org/packages/deemru/cloaked)

[Cloaked](https://github.com/deemru/Cloaked) implements cloaking for sensitive information, ensuring that it doesn’t fit a single 64 kilobyte packet.


## Usage

```php
$cloaked = new Cloaked();
$sensitive = 'Hello, world!';
$cloaked->cloak( $sensitive );
$cloaked->uncloak( function( $data ){ /*do stuff with*/ $data; } );
```

## Requirements

- [PHP](http://php.net) >= 5.6

## Installation

```bash
composer require deemru/cloaked
```
