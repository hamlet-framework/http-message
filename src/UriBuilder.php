<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

abstract class UriBuilder
{
    use UriValidatorTrait;

    /**
     * @var callable(string,string,string,int|null,string,string,string):Uri
     */
    private $constructor;

    /**
     * @var bool
     */
    private $validate;

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $userInfo = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var int|null
     */
    private $port = null;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @param callable(string,string,string,int|null,string,string,string):Uri $constructor
     * @param bool $validate
     */
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
        if ($this->validate) {
            $this->scheme = $this->normalizeScheme($scheme);
        } else {
            $this->scheme = $scheme;
        }
        return $this;
    }

    public function withUserInfo(string $user, string $password = null): self
    {
        $this->userInfo = $password === null || $password === '' ? $user : $user . ':' . $password;
        return $this;
    }

    public function withHost(string $host): self
    {
        if ($this->validate) {
            $this->host = $this->normalizeHost($host);
        } else {
            $this->host = $host;
        }
        return $this;
    }

    public function withPort(int $port): self
    {
        if ($this->validate) {
            $this->port = $this->normalizePort($port);
        } else {
            $this->port = $port;
        }
        return $this;
    }

    public function withPath(string $path): self
    {
        if ($this->validate) {
            $this->path = $this->normalizePath($path);
        } else {
            $this->path = $path;
        }

        return $this;
    }

    public function withQuery(string $query): self
    {
        if ($this->validate) {
            $this->query = $this->normalizeQuery($query);
        } else {
            $this->query = $query;
        }
        return $this;
    }

    public function withFragment(string $fragment): self
    {
        if ($this->validate) {
            $this->fragment = $this->normalizeFragment($fragment);
        } else {
            $this->fragment = $fragment;
        }
        return $this;
    }
}
