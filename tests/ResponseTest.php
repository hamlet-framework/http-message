<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\ResponseTestTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use ResponseTestTrait;

    protected function response(): ResponseInterface
    {
        return Response::empty();
    }

    protected function message(): MessageInterface
    {
        return $this->response();
    }

    protected function stream(): StreamInterface
    {
        return Stream::empty();
    }

    public function test_non_validating_builder_sets_values()
    {
        $response = Response::nonValidatingBuilder()
            ->withStatus(200, 'OK')
            ->build();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function test_validating_response_builder_raises_exception_on_invalid_status_code()
    {
        $this->expectException(InvalidArgumentException::class);
        Response::validatingBuilder()->withStatus(-1);
    }

    public function test_validating_response_builder_enriches_reason_phrase()
    {
        $response = Response::validatingBuilder()
            ->withStatus(200)
            ->build();
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function test_non_validating_response_builder_enriches_reason_phrase()
    {
        $response = Response::nonValidatingBuilder()
            ->withStatus(200)
            ->build();
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function test_no_mutation_on_identical_status_code()
    {
        $response = Response::empty()->withStatus(200);
        $this->assertSame($response, $response->withStatus(200, 'OK'));
    }
}
