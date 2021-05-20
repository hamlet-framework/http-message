<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class StreamFactoryTest extends TestCase
{
    public function test_create_stream()
    {
        $factory = new StreamFactory;
        $stream = $factory->createStream('this content');
        $stream->rewind();
        $this->assertEquals('this content', $stream->getContents());
    }

    public function test_create_stream_from_file()
    {
        $factory = new StreamFactory;
        $stream = $factory->createStreamFromFile(__FILE__);
        $stream->rewind();
        $this->assertStringStartsWith('<?php', $stream->getContents());
    }

    public function test()
    {
        $factory = new StreamFactory;
        $resource = fopen(__FILE__, 'r');
        $stream = $factory->createStreamFromResource($resource);
        $stream->rewind();
        $this->assertStringStartsWith('<?php', $stream->getContents());
    }
}
