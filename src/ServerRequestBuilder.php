<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\ServerRequestBuilderTrait;

abstract class ServerRequestBuilder
{
    use ServerRequestBuilderTrait;

    /**
     * @var callable(string|null,array<string,array<string>>|null,\Psr\Http\Message\StreamInterface|null,string|null,string|null,\Psr\Http\Message\UriInterface|null,array<string,string>|null,array<string,string>|null,array<string|int,mixed>|null,array<string,mixed>|null,array|object|null,bool,array<string,mixed>|null):ServerRequest
     */
    protected $constructor;

    /**
     * @param callable(string|null,array<string,array<string>>|null,\Psr\Http\Message\StreamInterface|null,string|null,string|null,\Psr\Http\Message\UriInterface|null,array<string,string>|null,array<string,string>|null,array<string|int,mixed>|null,array<string,mixed>|null,array|object|null,bool,array<string,mixed>|null):ServerRequest $constructor
     * @param bool $validate
     */
    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): ServerRequest
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body, $this->requestTarget, $this->method, $this->uri, $this->serverParams, $this->cookieParams, $this->queryParams, $this->uploadedFiles, $this->parsedBody, $this->parsedBodySet, $this->attributes);
    }
}
