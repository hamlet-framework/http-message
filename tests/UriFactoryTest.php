<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class UriFactoryTest extends TestCase
{
    public function test_create_uri()
    {
        $factory = new UriFactory;
        $uri = 'https://example.com:1345/test?x=3#fold';
        $this->assertEquals($uri, (string) $factory->createUri($uri));
    }
}
