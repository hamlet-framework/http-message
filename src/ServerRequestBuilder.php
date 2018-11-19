<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestBuilder extends RequestBuilder
{
    /**
     * @param array $serverParams
     * @return static
     */
    public function withServerParams(array $serverParams)
    {
        $this->properties['serverParams'] = $serverParams;
        return $this;
    }

    /**
     * @param array $cookieParams
     * @return static
     */
    public function withCookieParams(array $cookieParams)
    {
        $this->properties['cookieParams'] = $this->validate ? $this->validateCookieParams($cookieParams) : $cookieParams;
        return $this;
    }

    /**
     * @param array $queryParams
     * @return static
     */
    public function withQueryParams(array $queryParams)
    {
        $this->properties['queryParams'] = $this->validate ? $this->validateQueryParams($queryParams) : $queryParams;
        return $this;
    }

    /**
     * @param array $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->properties['uploadedFiles'] = $this->validate ? $this->validateUploadedFiles($uploadedFiles) : $uploadedFiles;
        return $this;
    }

    /**
     * @param array|object|null $body
     * @return static
     */
    public function withParsedBody($body)
    {
        $this->properties['parsedBody'] = $this->validate ? $this->validateParsedBody($body) : $body;
        return $this;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function withAttributes(array $attributes)
    {
        $this->properties['attributes'] = $this->validate ? $this->validateAttributes($attributes) : $attributes;
        return $this;
    }

    /**
     * @return ServerRequestInterface
     */
    public function build()
    {
        return ($this->constructor)($this->properties, $this->generators);
    }
}
