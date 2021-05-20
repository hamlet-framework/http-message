<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UriTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriTest extends TestCase
{
    use DataProviderTrait;
    use UriTestTrait;

    protected function uri($value = ''): UriInterface
    {
        return Uri::parse($value);
    }

    public function test_validating_uri_builder()
    {
        $uri = Uri::validatingBuilder()
            ->withHost('example.com')
            ->withPath('/test.php')
            ->withPort('457')
            ->withQuery('offset=3&limit=10')
            ->withScheme('https')
            ->withUserInfo('john', '123456')
            ->withFragment('paperfold')
            ->build();
        $this->assertEquals('https://john:123456@example.com:457/test.php?offset=3&limit=10#paperfold', (string) $uri);
    }

    public function test_non_validating_uri_builder()
    {
        $uri = Uri::nonValidatingBuilder()
            ->withHost('example.com')
            ->withPath('/test.php')
            ->withPort('457')
            ->withQuery('offset=3&limit=10')
            ->withScheme('https')
            ->withUserInfo('john', '123456')
            ->withFragment('paperfold')
            ->build();
        $this->assertEquals('https://john:123456@example.com:457/test.php?offset=3&limit=10#paperfold', (string) $uri);
    }
}
