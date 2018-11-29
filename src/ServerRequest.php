<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array<string,string>|null
     */
    protected $serverParams = null;

    /**
     * @var (callable():array<string,string>)|null
     */
    protected $serverParamsGenerator = null;

    /**
     * @var array<string,string>|null
     */
    protected $cookieParams;

    /**
     * @var (callable():array<string,string>)|null
     */
    protected $cookieParamsGenerator = null;

    /**
     * @var array<string|int,mixed>|null
     */
    protected $queryParams = null;

    /**
     * @var (callable():array<string|int,mixed>)|null
     */
    protected $queryParamsGenerator = null;

    /**
     * @var array<string,mixed>|null
     */
    protected $uploadedFiles = null;

    /**
     * @var (callable():array<string,mixed>)|null
     */
    protected $uploadedFilesGenerator = null;

    /**
     * @var array|object|null
     */
    protected $parsedBody = null;

    /**
     * @var bool
     */
    protected $parsedBodySet = false;

    /**
     * @var (callable():array|object|null)|null
     */
    protected $parsedBodyGenerator = null;

    /**
     * @var array<string,mixed>|null
     */
    protected $attributes = null;

    /**
     * @var (callable():array<string,mixed>)|null
     */
    protected $attributesGenerator = null;

    private static function serverRequestConstructor(): callable
    {
        $instance = new ServerRequest;
        return function ($protocolVersion, $headers, $body, $requestTarget, $method, $uri, $serverParams, $cookieParams, $queryParams, $uploadedFiles, $parsedBody, $parsedBodySet, $attributes) use ($instance): ServerRequest {
            $instance->protocolVersion = $protocolVersion;
            $instance->headers         = $headers;
            $instance->body            = $body;
            $instance->requestTarget   = $requestTarget;
            $instance->method          = $method;
            $instance->uri             = $uri;
            $instance->serverParams    = $serverParams;
            $instance->cookieParams    = $cookieParams;
            $instance->queryParams     = $queryParams;
            $instance->uploadedFiles   = $uploadedFiles;
            $instance->parsedBody      = $parsedBody;
            $instance->parsedBodySet   = $parsedBodySet;
            $instance->attributes      = $attributes;
            return $instance;
        };
    }

    /**
     * @return ServerRequestBuilder
     */
    public static function validatingBuilder()
    {
        $constructor = self::serverRequestConstructor();
        return new class($constructor, true) extends ServerRequestBuilder {
        };
    }

    /**
     * @return ServerRequestBuilder
     */
    public static function nonValidatingBuilder()
    {
        $constructor = self::serverRequestConstructor();
        return new class($constructor, false) extends ServerRequestBuilder {
        };
    }

    public function getServerParams(): array
    {
        if (!isset($this->serverParams)) {
            if (isset($this->serverParamsGenerator)) {
                $this->serverParams = ($this->serverParamsGenerator)();
                $this->serverParamsGenerator = null;
            } else {
                $this->serverParams = [];
            }
        }
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        if (!isset($this->cookieParams)) {
            if (isset($this->cookieParamsGenerator)) {
                $this->cookieParams = ($this->cookieParamsGenerator)();
                $this->cookieParamsGenerator = null;
            } else {
                $this->cookieParams = [];
            }
        }
        return $this->cookieParams;
    }

    /**
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $copy = clone $this;
        $copy->cookieParams = $this->validateCookieParams($cookies);
        $copy->cookieParamsGenerator = null;
        return $copy;
    }

    public function getQueryParams(): array
    {
        if (!isset($this->queryParams)) {
            if (isset($this->queryParamsGenerator)) {
                $this->queryParams = ($this->queryParamsGenerator)();
                $this->queryParamsGenerator = null;
            } else {
                $this->queryParams = [];
            }
        }
        return $this->queryParams;
    }

    /**
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $copy = clone $this;
        $copy->queryParams = $this->validateQueryParams($query);
        $copy->queryParamsGenerator = null;
        return $copy;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
        if (!isset($this->uploadedFiles)) {
            if (isset($this->uploadedFilesGenerator)) {
                $this->uploadedFiles = ($this->uploadedFilesGenerator)();
                $this->uploadedFilesGenerator = null;
            } else {
                $this->uploadedFiles = [];
            }
        }
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles
     * @return static
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $copy = clone $this;
        $copy->uploadedFiles = $this->validateUploadedFiles($uploadedFiles);
        $copy->uploadedFilesGenerator = null;
        return $copy;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody()
    {
        if (!$this->parsedBodySet) {
            if ($this->parsedBodyGenerator) {
                $this->parsedBody = ($this->parsedBodyGenerator)();
            }
            $this->parsedBodySet = true;
        }
        return $this->parsedBody;
    }

    /**
     * @param array|object|null $data
     * @return static
     * @throws InvalidArgumentException
     */
    public function withParsedBody($data)
    {
        $copy = clone $this;
        $copy->parsedBody = $this->validateParsedBody($data);
        $copy->parsedBodySet = true;
        $copy->parsedBodyGenerator = null;
        return $copy;
    }

    /**
     * @return array<string,mixed>
     */
    public function getAttributes(): array
    {
        if (!isset($this->attributes)) {
            if (isset($this->attributesGenerator)) {
                $this->attributes = ($this->attributesGenerator)();
                $this->attributesGenerator = null;
            } else {
                $this->attributes = [];
            }
        }
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $attributes = $this->getAttributes();
        if (\array_key_exists($name, $attributes)) {
            return $attributes[$name];
        }
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $this->validateAttributeName($name);

        $attributes = $this->getAttributes();
        $attributes[$name] = $value;

        $copy = clone $this;
        $copy->attributes = $this->validateAttributes($attributes);
        $copy->attributesGenerator = null;
        return $copy;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        $this->validateAttributeName($name);

        $attributes = $this->getAttributes();
        if (!\array_key_exists($name, $attributes)) {
            return $this;
        }
        unset($attributes[$name]);

        $copy = clone $this;
        $copy->attributes = $attributes;
        $copy->attributesGenerator = null;
        return $copy;
    }
}
