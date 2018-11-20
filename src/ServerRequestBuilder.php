<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestBuilder extends RequestBuilder
{
    /** @var array|null */
    protected $serverParams = null;

    /** @var array|null */
    protected $cookieParams = null;

    /** @var array|null */
    protected $queryParams = null;

    /** @var array|null */
    protected $uploadedFiles = null;

    /** @var object|array|null */
    protected $parsedBody = null;

    /** @var bool */
    protected $parsedBodySet = false;

    /** @var array|null */
    protected $attributes = null;

    /**
     * @param array $serverParams
     * @return static
     */
    public function withServerParams(array $serverParams)
    {
        $this->serverParams = $serverParams;
        return $this;
    }

    /**
     * @param array $cookieParams
     * @return static
     */
    public function withCookieParams(array $cookieParams)
    {
        $this->cookieParams = $this->validate ? $this->validateCookieParams($cookieParams) : $cookieParams;
        return $this;
    }

    /**
     * @param array $queryParams
     * @return static
     */
    public function withQueryParams(array $queryParams)
    {
        $this->queryParams = $this->validate ? $this->validateQueryParams($queryParams) : $queryParams;
        return $this;
    }

    /**
     * @param array $uploadedFiles
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
     * @return static
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $this->validate ? $this->validateAttributes($attributes) : $attributes;
        return $this;
    }

    /**
     * @return ServerRequestInterface
     */
    public function build()
    {
        return ($this->constructor)(
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $this->requestTarget,
            $this->method,
            $this->uri,
            $this->serverParams,
            $this->cookieParams,
            $this->queryParams,
            $this->uploadedFiles,
            $this->parsedBody,
            $this->parsedBodySet,
            $this->attributes
        );
    }
}
