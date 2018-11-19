<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @param bool $validate
     * @return RequestBuilder
     */
    protected static function builder(bool $validate)
    {
        $instance = new static;
        $constructor = function (array &$properties, array &$generators) use ($instance) {
            $instance->properties = $properties;
            $instance->generators = $generators;
            return $instance;
        };
        return new class($constructor, $validate) extends RequestBuilder {};
    }

    /**
     * @return RequestBuilder
     */
    public static function validatingBuilder()
    {
        return self::builder(true);
    }

    /**
     * @return RequestBuilder
     */
    public static function nonValidatingBuilder()
    {
        return self::builder(false);
    }

    public function getRequestTarget(): string
    {
        if (\array_key_exists('requestTarget', $this->properties)) {
            return $this->properties['requestTarget'];
        }

        if (\array_key_exists('requestTarget', $this->generators)) {
            return $this->properties['requestTarget'] = call_user_func(...$this->generators['requestTarget']);
        }

        /** @var UriInterface|null */
        $uri = $this->fetch('uri');

        if ($uri === null) {
            $requestTarget = '/';
        } else {
            $path = $uri->getPath();
            $query = $uri->getQuery();
            if ($path === '') {
                if ($query === '') {
                    $requestTarget = '/';
                } else {
                    $requestTarget = '/?' . $query;
                }
            } else {
                if ($query === '') {
                    $requestTarget = $path;
                } else {
                    $requestTarget = $path . '?' . $query;
                }
            }
        }

        return $this->properties['requestTarget'] = $requestTarget;
    }

    /**
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['requestTarget'] = [[&$this, 'replaceRequestTarget'], &$requestTarget];
        return $request;
    }

    public function getMethod(): string
    {
        return $this->fetch('method', 'GET');
    }

    /**
     * @param string $method
     * @return static
     * @throws InvalidArgumentException
     */
    public function withMethod($method)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['method'] = [[&$this, 'replaceMethod'], &$method];
        return $request;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        $uri = $this->fetch('uri');
        if ($uri !== null) {
            return $uri;
        }
        return $this->properties['uri'] = Uri::empty();
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $request = new static;
        $request->parent = &$this;
        $request->properties['uri'] = &$uri;
        if (!$preserveHost) {
            $request->generators['headers'] = [[&$this, 'removeHeader'], 'host'];
        }
        return $request;
    }

    protected function replaceRequestTarget($requestTarget)
    {
        return $this->validateRequestTarget($requestTarget);
    }

    protected function replaceMethod($method)
    {
        return $this->validateMethod($method);
    }
}
