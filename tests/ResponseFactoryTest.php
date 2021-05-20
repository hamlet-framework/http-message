<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    public function test_create_response()
    {
        $factory = new ResponseFactory;
        $response = $factory->createResponse(123, 'Hello');
        $this->assertEquals(123, $response->getStatusCode());
        $this->assertEquals('Hello', $response->getReasonPhrase());
    }
}
