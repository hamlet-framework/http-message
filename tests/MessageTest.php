<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class MessageTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;

    protected function message(): MessageInterface
    {
        return Message::empty();
    }

    protected function stream(): StreamInterface
    {
        return Stream::empty();
    }

    public function test_non_validating_builder_sets_values()
    {
        $body = Stream::empty();
        $headers = [
            'Host' => ['example.com']
        ];

        $message = Message::nonValidatingBuilder()
            ->withProtocolVersion('1.1')
            ->withBody($body)
            ->withHeaders($headers)
            ->build();

        $this->assertSame('1.1', $message->getProtocolVersion());
        $this->assertSame($body, $message->getBody());
        $this->assertSame($headers, $message->getHeaders());
    }

    public function test_validating_message_builder_raises_error_on_invalid_protocol_version()
    {
        $this->expectException(InvalidArgumentException::class);
        Message::validatingBuilder()->withProtocolVersion('test');
    }

    public function test_validating_message_builder_moves_host_at_the_top()
    {
        $headers = [
            'a' => 'a',
            'host' => 'www.example.net'
        ];

        $message = Message::validatingBuilder()
            ->withHeaders($headers)
            ->build();

        $this->assertEquals(['Host', 'a'], array_keys($message->getHeaders()));
    }

    public function test_with_headers_merges_different_spellings()
    {
        $message = Message::validatingBuilder()
            ->withHeaders([
                'Header' => 1,
                'HeADeR' => 2,
                'headeR' => 'x'
            ])
            ->build();
        $this->assertEquals([1, 2, 'x'], $message->getHeader('header'));
    }
}
