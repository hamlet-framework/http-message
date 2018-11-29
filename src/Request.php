<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @var string|null
     */
    protected $requestTarget = null;

    /**
     * @var string|null
     */
    protected $method = null;

    /**
     * @var (callable():string)|null
     */
    protected $methodGenerator = null;

    /**
     * @var UriInterface|null
     */
    protected $uri = null;

    /**
     * @var (callable():UriInterface)|null
     */
    protected $uriGenerator = null;

    private static function requestConstructor(): callable
    {
        $instance = new Request;
        return function ($protocolVersion, $headers, $body, $requestTarget, $method, $uri) use ($instance): Request {
            $instance->protocolVersion = $protocolVersion;
            $instance->headers = $headers;
            $instance->body = $body;
            $instance->requestTarget = $requestTarget;
            $instance->method = $method;
            $instance->uri = $uri;
            return $instance;
        };
    }

    /**
     * @return RequestBuilder
     */
    public static function validatingBuilder()
    {
        $constructor = self::requestConstructor();
        return new class($constructor, true) extends RequestBuilder {
        };
    }

    /**
     * @return RequestBuilder
     */
    public static function nonValidatingBuilder()
    {
        $constructor = self::requestConstructor();
        return new class($constructor, false) extends RequestBuilder {
        };
    }

    public function withoutHeader($name)
    {
        $headers = $this->getHeaders();
        $normalizedName = $this->normalizeHeaderName($name);

        if (!isset($headers[$normalizedName])) {
            return $this;
        }

        $copy = clone $this;
        assert($copy->headers !== null);
        unset($copy->headers[$normalizedName]);
        if ($normalizedName == 'Host') {
            $this->addHostHeader($copy);
        }
        return $copy;
    }

    public function getRequestTarget(): string
    {
        if (!isset($this->requestTarget)) {
            $uri = $this->getUri();
            $path = $uri->getPath();
            $query = $uri->getQuery();
            if ($path === '') {
                if ($query === '') {
                    $this->requestTarget = '/';
                } else {
                    $this->requestTarget = '/?' . $query;
                }
            } else {
                if ($query === '') {
                    $this->requestTarget = $path;
                } else {
                    $this->requestTarget = $path . '?' . $query;
                }
            }
        }
        return $this->requestTarget;
    }

    /**
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        if ($this->getRequestTarget() === $requestTarget) {
            return $this;
        }

        $copy = clone $this;
        $copy->requestTarget = $this->validateRequestTarget($requestTarget);
        return $copy;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        if (!isset($this->uri)) {
            if (isset($this->uriGenerator)) {
                $this->uri = ($this->uriGenerator)();
                $this->uriGenerator = null;
            } else {
                $this->uri = Uri::empty();
            }
        }
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $copy = clone $this;
        $copy->uri = $uri;
        $copy->uriGenerator = null;
        $headers = $copy->getHeaders();
        if (!isset($headers['Host']) || !$preserveHost) {
            $this->addHostHeader($copy);
        }
        return $copy;
    }

    public function getMethod(): string
    {
        if (!isset($this->method)) {
            if (isset($this->methodGenerator)) {
                $this->method = ($this->methodGenerator)();
                $this->methodGenerator = null;
            } else {
                $this->method = 'GET';
            }
        }
        return $this->method;
    }

    /**
     * @param string $method
     * @return static
     * @throws InvalidArgumentException
     */
    public function withMethod($method)
    {
        if ($this->getMethod() === $method) {
            return $this;
        }

        $copy = clone $this;
        $copy->method = $this->validateMethod($method);
        $copy->methodGenerator = null;
        return $copy;
    }

    private function addHostHeader(Request &$request): void
    {
        $headers = $this->getHeaders();
        $uri = $request->getUri();
        $host = $uri->getHost();
        if ($host !== '') {
            $port = $uri->getPort();
            $hostWithPort = $port ? $host . ':' . $port : $host;
            if (isset($headers['Host'])) {
                $request->headers['Host'] = [$hostWithPort];
            } else {
                $request->headers = ['Host' => [$hostWithPort]] + $headers;
                $request->headerNames = ['host' => 'Host'];
            }
        }
    }
}
