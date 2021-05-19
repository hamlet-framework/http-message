<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use Hamlet\Http\Message\Spec\Traits\ServerRequestTestTrait;
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
        $serverParams = ['REQUEST_URI' => '/index.php'];
        $query = ['offset' => '33'];
        $body = new \stdClass();
        $uploadedFiles = ['test' => []];
        $cookies = ['PHP_SESSION_ID', '1'];
        $attributes = ['a' => 123];

        $request = ServerRequest::nonValidatingBuilder()
            ->withServerParams($serverParams)
            ->withQueryParams($query)
            ->withParsedBody($body)
            ->withUploadedFiles($uploadedFiles)
            ->withCookieParams($cookies)
            ->withAttributes($attributes)
            ->build();

        $this->assertSame($serverParams, $request->getServerParams());
        $this->assertSame($query, $request->getQueryParams());
        $this->assertSame($body, $request->getParsedBody());
        $this->assertSame($uploadedFiles, $request->getUploadedFiles());
        $this->assertSame($cookies, $request->getCookieParams());
        $this->assertSame($attributes, $request->getAttributes());
    }
}
