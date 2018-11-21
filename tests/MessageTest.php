<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use PHPUnit\Framework\Assert;
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

        $message = Message::nonValidatingMessageBuilder()
            ->withProtocolVersion('1.1')
            ->withBody($body)
            ->withHeaders($headers)
            ->build();

        Assert::assertSame('1.1', $message->getProtocolVersion());
        Assert::assertSame($body, $message->getBody());
        Assert::assertSame($headers, $message->getHeaders());
    }

    public function test_validating_message_builder_moves_host_at_the_top()
    {
        $headers = [
            'a' => 'a',
            'host' => 'www.example.net'
        ];

        $message = Message::validatingMessageBuilder()
            ->withHeaders($headers)
            ->build();

        Assert::assertEquals(['Host', 'a'], array_keys($message->getHeaders()));
    }
}
