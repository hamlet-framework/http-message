<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    public function test_create_request()
    {
        $factory = new RequestFactory;
        $request = $factory->createRequest('PUT', 'https://example.com:345/test');
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('https://example.com:345/test', (string) $request->getUri());
    }
}
