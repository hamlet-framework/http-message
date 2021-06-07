<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Create a new server request.
     *
     * Note that server parameters are taken precisely as given - no parsing/processing
     * of the given values is performed. In particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     * @param array $serverParams An array of Server API (SAPI) parameters with
     *     which to seed the generated request instance.
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = Uri::parse($uri);
        }
        return ServerRequest::validatingBuilder()
            ->withMethod($method)
            ->withUri($uri)
            ->withServerParams($serverParams)
            ->build();
    }
}
