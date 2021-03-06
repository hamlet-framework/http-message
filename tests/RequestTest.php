<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    protected function message(): Message
    {
        return $this->request();
    }

    protected function request(): Request
    {
        return Request::empty();
    }

    protected function stream(): StreamInterface
    {
        return Stream::empty();
    }

    protected function uri(string $value): UriInterface
    {
        return Uri::parse($value);
    }

    public function test_non_validating_builder_sets_values()
    {
        $uri = Uri::parse('http://example.com');

        $request = Request::nonValidatingBuilder()
            ->withRequestTarget('*')
            ->withMethod('PUT')
            ->withUri($uri)
            ->build();

        $this->assertSame('*', $request->getRequestTarget());
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame($uri, $request->getUri());
    }

    public function test_validating_builder_sets_values()
    {
        $uri = Uri::parse('http://example.com');

        $request = Request::validatingBuilder()
            ->withRequestTarget('*')
            ->withMethod('PUT')
            ->withUri($uri)
            ->build();

        $this->assertSame('*', $request->getRequestTarget());
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame($uri, $request->getUri());
    }

    public function test_validating_builder_raises_exception_on_invalid_method()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::validatingBuilder()->withMethod('THE GET');
    }

    public function test_validating_builder_raises_exception_on_invalid_request_target()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::validatingBuilder()->withRequestTarget('request target');
    }

    public function test_keeping_method_does_not_mutate()
    {
        $method = 'POST';
        $request = Request::empty()->withMethod($method);
        $this->assertSame($request, $request->withMethod($method));
    }

    public function test_keeping_request_target_does_not_mutate()
    {
        $requestTarget = '123';
        $request = Request::empty()->withRequestTarget($requestTarget);
        $this->assertSame($request, $request->withRequestTarget($requestTarget));
    }

    public function test_wihout_non_existing_headers_does_not_mutate()
    {
        $request = Request::empty();
        $this->assertSame($request, $request->withoutHeader('Host'));
    }
}
