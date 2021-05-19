<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

/**
 * @psalm-import-type Server from Message
 * @psalm-import-type Cookies from Message
 * @psalm-import-type Get from Message
 * @psalm-import-type Files from Message
 * @psalm-import-type ParsedBody from Message
 * @psalm-import-type Attributes from Message
 */
trait ServerRequestBuilderTrait
{
    use RequestBuilderTrait;

    /**
     * @var Server|null
     */
    protected $serverParams = null;

    /**
     * @var Cookies|null
     */
    protected $cookieParams = null;

    /**
     * @var Get|null
     */
    protected $queryParams = null;

    /**
     * @var Files|null
     */
    protected $uploadedFiles = null;

    /**
     * @var ParsedBody|null
     */
    protected $parsedBody = null;

    /**
     * @var bool
     */
    protected $parsedBodySet = false;

    /**
     * @var Attributes|null
     */
    protected $attributes = null;

    /**
     * @param Server $serverParams
     * @return static
     */
    public function withServerParams(array $serverParams)
    {
        $this->serverParams = $this->validate ? $this->validateServerParams($serverParams) : $serverParams;
        return $this;
    }

    /**
     * @param Cookies $cookieParams
     * @return static
     */
    public function withCookieParams(array $cookieParams)
    {
        $this->cookieParams = $this->validate ? $this->validateCookieParams($cookieParams) : $cookieParams;
        return $this;
    }

    /**
     * @param Get $queryParams
     * @return static
     */
    public function withQueryParams(array $queryParams)
    {
        $this->queryParams = $this->validate ? $this->validateQueryParams($queryParams) : $queryParams;
        return $this;
    }

    /**
     * @param Files $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->uploadedFiles = $this->validate ? $this->validateUploadedFiles($uploadedFiles) : $uploadedFiles;
        return $this;
    }

    /**
     * @param ParsedBody|null $body
     * @return static
     */
    public function withParsedBody($body)
    {
        $this->parsedBody = $this->validate ? $this->validateParsedBody($body) : $body;
        $this->parsedBodySet = true;
        return $this;
    }

    /**
     * @param Attributes $attributes
     * @return static
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $this->validate ? $this->validateAttributes($attributes) : $attributes;
        return $this;
    }
}
