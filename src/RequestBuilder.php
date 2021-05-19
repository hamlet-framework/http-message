<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-import-type Headers from Message
 */
abstract class RequestBuilder
{
    use RequestBuilderTrait;

    /**
     * @var callable(string|null,Headers|null,StreamInterface|null,string|null,string|null,UriInterface|null):Request
     */
    protected $constructor;

    /**
     * @param callable(string|null,Headers|null,StreamInterface|null,string|null,string|null,UriInterface|null):Request $constructor
     * @param bool $validate
     */
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
