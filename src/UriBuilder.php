<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\UriValidatorTrait;

abstract class UriBuilder
{
    use UriValidatorTrait;

    /** @var callable */
    private $constructor;

    /** @var bool */
    private $validate;

    /** @var string */
    private $scheme = '';

    /** @var string */
    private $userInfo = '';

    /** @var string */
    private $host = '';

    /** @var int|null */
    private $port = null;

    /** @var string */
    private $path = '';

    /** @var string */
    private $query = '';

    /** @var string */
    private $fragment = '';

    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): Uri
    {
        return ($this->constructor)($this->scheme, $this->userInfo, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    public function withScheme(string $scheme): self
    {
        $this->scheme = $this->validate ? $this->normalizeScheme($scheme) : $scheme;
        return $this;
    }

    public function withUserInfo(string $user, string $password = null): self
    {
        $this->userInfo = $password === null || $password === '' ? $user : $user . ':' . $password;
        return $this;
    }

    public function withHost(string $host): self
    {
        $this->host = $this->validate ? $this->normalizeHost($host) : $host;
        return $this;
    }

    public function withPort(int $port): self
    {
        $this->port = $this->validate ? $this->normalizePort($port) : $port;
        return $this;
    }

    public function withPath(string $path): self
    {
        $this->path = $this->validate ? $this->normalizePath($path) : $path;
        return $this;
    }

    public function withQuery(string $query): self
    {
        $this->query = $this->validate ? $this->normalizeQuery($query) : $query;
        return $this;
    }

    public function withFragment(string $fragment): self
    {
        $this->fragment = $this->validate ? $this->normalizeFragment($fragment) : $fragment;
        return $this;
    }
}
