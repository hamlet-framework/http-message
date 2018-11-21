<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\ServerRequestBuilderTrait;

abstract class ServerRequestBuilder
{
    use ServerRequestBuilderTrait;

    /**
     * @var callable
     */
    protected $constructor;

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
