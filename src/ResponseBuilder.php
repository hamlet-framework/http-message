<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\ResponseBuilderTrait;

abstract class ResponseBuilder
{
    use ResponseBuilderTrait;

    /**
     * @var callable(string|null,array<string,array<string>>|null,\Psr\Http\Message\StreamInterface|null,int|null,string|null):Response
     */
    protected $constructor;

    /**
     * @param callable(string|null,array<string,array<string>>|null,\Psr\Http\Message\StreamInterface|null,int|null,string|null):Response $constructor
     * @param bool $validate
     */
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
