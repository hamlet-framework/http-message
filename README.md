# Hamlet / HTTP / Message

[![Build Status](https://travis-ci.org/hamlet-framework/http-message.svg?branch=master)](https://travis-ci.org/hamlet-framework/http-message)

This library generally provides you with three ways of creating objects.

## Step-wise adjustments

The "standard" approach is to create an object through a series of adjustments.

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

When creating messages within you app's "secure boundaries", there is often a way to avoid redundant argument validation by using non-validating builders

```php
$message = Message::nonValidatingBuilder()
    ->withProtocolVersion('1.1')
    ->withBody($body)
    ->withHeaders($headers)
    ->build();
```

Please note, that with non-validating builders you need to know what you're doing. When in doubt use validating builders.

## Outstanding tasks

- Adapt tests from https://github.com/Sporkmonger/Addressable/blob/master/spec/addressable/uri_spec.rb
