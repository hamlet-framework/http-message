<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class ServerRequestFactoryTest extends TestCase
{
    public function test_create_server_request()
    {
        $factory = new ServerRequestFactory;
        $request = $factory->createServerRequest('PUT', 'https://example.com:123/test', ['a' => 'b']);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('https://example.com:123/test', (string) $request->getUri());
        $this->assertEquals(['a' => 'b'], $request->getServerParams());
    }
}
