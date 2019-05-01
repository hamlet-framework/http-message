<?php

namespace Hamlet\Http\Message\Traits;

trait ServerRequestBuilderTrait
{
    use RequestBuilderTrait;

    /**
     * @var array|null
     * @psalm-var array<string,string>|null
     */
    protected $serverParams = null;

    /**
     * @var array|null
     * @psalm-var array<string,string>|null
     */
    protected $cookieParams = null;

    /**
     * @var array|null
     * @psalm-var array<string|int,mixed>|null
     */
    protected $queryParams = null;

    /**
     * @var array|null
     * @psalm-var array<string,mixed>|null
     */
    protected $uploadedFiles = null;

    /**
     * @var object|array|null
     */
    protected $parsedBody = null;

    /**
     * @var bool
     */
    protected $parsedBodySet = false;

    /**
     * @var array|null
     * @psalm-var array<string,mixed>|null
     */
    protected $attributes = null;

    /**
     * @param array $serverParams
     * @psalm-param array<string,string> $serverParams
     * @return static
     */
    public function withServerParams(array $serverParams)
    {
        $this->serverParams = $this->validate ? $this->validateServerParams($serverParams) : $serverParams;
        return $this;
    }

    /**
     * @param array $cookieParams
     * @psalm-param array<string,string> $cookieParams
     * @return static
     */
    public function withCookieParams(array $cookieParams)
    {
        $this->cookieParams = $this->validate ? $this->validateCookieParams($cookieParams) : $cookieParams;
        return $this;
    }

    /**
     * @param array $queryParams
     * @psalm-param array<string|int,mixed> $queryParams
     * @return static
     */
    public function withQueryParams(array $queryParams)
    {
        $this->queryParams = $this->validate ? $this->validateQueryParams($queryParams) : $queryParams;
        return $this;
    }

    /**
     * @param array $uploadedFiles
     * @psalm-param array<string,mixed> $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->uploadedFiles = $this->validate ? $this->validateUploadedFiles($uploadedFiles) : $uploadedFiles;
        return $this;
    }

    /**
     * @param array|object|null $body
     * @return static
     */
    public function withParsedBody($body)
    {
        $this->parsedBody = $this->validate ? $this->validateParsedBody($body) : $body;
        $this->parsedBodySet = true;
        return $this;
    }

    /**
     * @param array $attributes
     * @psalm-param array<string,mixed> $attributes
     * @return static
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $this->validate ? $this->validateAttributes($attributes) : $attributes;
        return $this;
    }
}
