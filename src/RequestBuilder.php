<?php

namespace Hamlet\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestBuilder extends MessageBuilder
{
    /**
     * @param string $target
     * @return static
     */
    public function withRequestTarget(string $target)
    {
        $this->properties['requestTarget'] = $this->validate ? $this->validateRequestTarget($target) : $target;
        return $this;
    }

    /**
     * @param string $method
     * @return static
     */
    public function withMethod(string $method)
    {
        $this->properties['method'] = $this->validate ? $this->validateMethod($method) : $method;
        return $this;
    }

    /**
     * @param UriInterface $uri
     * @return static
     */
    public function withUri(UriInterface $uri)
    {
        $this->properties['uri'] = $uri;
        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function build()
    {
        return ($this->constructor)($this->properties, $this->generators);
    }
}
