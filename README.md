# Hamlet / HTTP / Message

![CI Status](https://github.com/hamlet-framework/type/workflows/CI/badge.svg?branch=master&event=push)
[![Packagist](https://img.shields.io/packagist/v/hamlet-framework/http-message.svg)](https://packagist.org/packages/hamlet-framework/http-message)
[![Packagist](https://img.shields.io/packagist/dt/hamlet-framework/http-message.svg)](https://packagist.org/packages/hamlet-framework/http-message)
[![Coverage Status](https://coveralls.io/repos/github/hamlet-framework/http-message/badge.svg?branch=master)](https://coveralls.io/github/hamlet-framework/http-message?branch=master)
![Psalm coverage](https://shepherd.dev/github/hamlet-framework/http-message/coverage.svg?)

PSR-7 and PSR-17 implementation.

This library generally provides you with three ways of creating objects.

## Step-wise adjustments

The standard approach is to create an object through a series of adjustments.

```php
$message = Message::empty()
    ->withProtocolVersion('1.1')
    ->withHeader('Host', 'example.net');
```

Note, that all `with*` methods are validating and in the example above we're creating 2 intermediate objects along the way.

## Validating builders

We can avoid creating multiple objects by using a validating builder

```php
$message = Message::validatingBuilder()
    ->withProtocolVersion('1.1')
    ->withBody($body)
    ->withHeaders($headers)
    ->build();
```

It offers the same level of validation as the previous method.

## Non-validating builders

When creating messages within you application's secure boundaries, there is a way to avoid redundant argument validation by using non-validating builders

```php
$message = Message::nonValidatingBuilder()
    ->withProtocolVersion('1.1')
    ->withBody($body)
    ->withHeaders($headers)
    ->build();
```

When in doubt use validating builders.

## Outstanding tasks

- Adapt tests from https://github.com/Sporkmonger/Addressable/blob/master/spec/addressable/uri_spec.rb
- Expand test coverage
