<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\ResponseBuilderTrait;

abstract class ResponseBuilder
{
    use ResponseBuilderTrait;

    /**
     * @var callable
     */
    protected $constructor;

    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): Response
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body, $this->statusCode, $this->reasonPhrase);
    }
}
