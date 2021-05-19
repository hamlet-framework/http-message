<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-import-type Headers from Message
 * @psalm-import-type Server from Message
 * @psalm-import-type Cookies from Message
 * @psalm-import-type Get from Message
 * @psalm-import-type Files from Message
 * @psalm-import-type ParsedBody from Message
 * @psalm-import-type Attributes from Message
 */
abstract class ServerRequestBuilder
{
    use ServerRequestBuilderTrait;

    /**
     * @var callable(string|null,Headers|null,StreamInterface|null,string|null,string|null,UriInterface|null,Server|null,Cookies|null,Get|null,Files|null,ParsedBody|null,bool,Attributes|null):ServerRequest
     */
    protected $constructor;

    /**
     * @param callable(string|null,Headers|null,StreamInterface|null,string|null,string|null,UriInterface|null,Server|null,Cookies|null,Get|null,Files|null,ParsedBody|null,bool,Attributes|null):ServerRequest $constructor
     * @param bool $validate
     */
    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): ServerRequest
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body, $this->requestTarget, $this->method, $this->uri, $this->serverParams, $this->cookieParams, $this->queryParams, $this->uploadedFiles, $this->parsedBody, $this->parsedBodySet, $this->attributes);
    }
}
