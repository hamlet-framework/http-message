<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\RequestBuilderTrait;

abstract class RequestBuilder
{
    use RequestBuilderTrait;

    /**
     * @var callable
     */
    protected $constructor;

    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): Request
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body, $this->requestTarget, $this->method, $this->uri);
    }
}
