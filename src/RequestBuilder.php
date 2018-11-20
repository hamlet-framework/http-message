<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestBuilder extends MessageBuilder
{
    /** @var string|null */
    protected $requestTarget = null;

    /** @var string|null */
    protected $method = null;

    /** @var UriInterface|null */
    protected $uri = null;

    /**
     * @param string $target
     * @return static
     */
    public function withRequestTarget(string $target)
    {
        $this->requestTarget = $this->validate ? $this->validateRequestTarget($target) : $target;
        return $this;
    }

    /**
     * @param string $method
     * @return static
     */
    public function withMethod(string $method)
    {
        $this->method = $this->validate ? $this->validateMethod($method) : $method;
        return $this;
    }

    /**
     * @param UriInterface $uri
     * @return static
     */
    public function withUri(UriInterface $uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function build()
    {
        return ($this->constructor)(
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $this->requestTarget,
            $this->method,
            $this->uri
        );
    }
}
