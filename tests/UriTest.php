<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UriTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriTest extends TestCase
{
    use DataProviderTrait;
    use UriTestTrait;

    protected function uri($value = ''): UriInterface
    {
        return Uri::parse($value);
    }
}
