<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use Hamlet\Http\Message\Spec\Traits\ServerRequestTestTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

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
        $body = new stdClass();
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

    public function test_validating_builder_sets_values()
    {
        $serverParams = ['REQUEST_URI' => '/index.php'];
        $query = ['offset' => '33'];
        $body = new stdClass();
        $uploadedFile = UploadedFile::builder()
            ->withStream(Stream::fromString('abc'))
            ->withSize(3)
            ->withErrorStatus(UPLOAD_ERR_OK)
            ->build();
        $uploadedFiles = ['test' => ['a' => $uploadedFile]];
        $cookies = ['PHP_SESSION_ID' => '1'];
        $attributes = ['a' => 123];

        $request = ServerRequest::validatingBuilder()
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

    public function test_validating_builder_raises_exception_on_invalid_server_params()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withServerParams([1 => new stdClass]);
    }

    public function test_validating_builder_raises_exception_on_invalid_cookies()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withCookieParams([1 => new stdClass]);
    }

    public function test_validating_builder_raises_exception_on_invalid_query_params()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withQueryParams([1 => new stdClass]);
    }

    public function test_validating_builder_raises_exception_on_invalid_uploaded_files()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withUploadedFiles([1 => new stdClass]);
    }

    public function test_validating_builder_raises_exception_on_invalid_parsed_body()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withParsedBody(true);
    }

    public function test_validating_builder_raises_exception_on_invalid_attributes()
    {
        $this->expectException(InvalidArgumentException::class);
        ServerRequest::validatingBuilder()->withServerParams([new stdClass]);
    }
}
