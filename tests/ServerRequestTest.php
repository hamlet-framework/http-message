<?php

namespace Hamlet\Http\Message;

use function Hamlet\Cast\_string;
use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use Hamlet\Http\Message\Spec\Traits\ServerRequestTestTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;
    use ServerRequestTestTrait;

    protected function serverRequest(): ServerRequestInterface
    {
        return ServerRequest::empty();
    }

    protected function message(): MessageInterface
    {
        return $this->serverRequest();
    }

    protected function request(): RequestInterface
    {
        return $this->serverRequest();
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
        $body = new \stdClass();

        $request = ServerRequest::nonValidatingBuilder()
            ->withParsedBody($body)
            ->build();

        Assert::assertSame($body, $request->getParsedBody());
    }

    public function test_get_query_param_with_type()
    {
        $request = ServerRequest::nonValidatingBuilder()
            ->build();

        $param = $request->getQueryParam('name', _string(), 'Anatoly');
        Assert::assertEquals('Anatoly', $param);
    }
}
