<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\UriValidatorTrait;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use function is_string;
use function ltrim;
use function parse_url;

class Uri implements UriInterface
{
    const STANDARD_PORTS = [
        'http'  => 80,
        'https' => 443
    ];

    use UriValidatorTrait;

    /** @var string */
    private $scheme = '';

    /** @var string|null */
    private $authority = null;

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

    /** @var string|null */
    private $literal = null;

    private function __construct()
    {
    }

    public static function validatingBuilder(): UriBuilder
    {
        return self::builder(true);
    }

    public static function nonValidatingBuilder(): UriBuilder
    {
        return self::builder(false);
    }

    private static function builder(bool $validate = true): UriBuilder
    {
        $instance = new self;

        $constructor =
            /**
             * @param string   $scheme
             * @param string   $userInfo
             * @param string   $host
             * @param int|null $port
             * @param string   $path
             * @param string   $query
             * @param string   $fragment
             * @return Uri
             */
            function (
                string $scheme,
                string $userInfo,
                string $host,
                $port,
                string $path,
                string $query,
                string $fragment
            ) use ($instance): Uri {
                $instance->scheme   = $scheme;
                $instance->userInfo = $userInfo;
                $instance->host     = $host;
                $instance->port     = $port;
                $instance->path     = $path;
                $instance->query    = $query;
                $instance->fragment = $fragment;
                return $instance;
            };
        return new class($constructor, $validate) extends UriBuilder {
        };
    }

    public static function empty(): Uri
    {
        return new self;
    }

    /**
     * @param string $uri
     * @return Uri
     */
    public static function parse($uri): Uri
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($uri)) {
            throw new InvalidArgumentException('URI needs to be a string');
        }
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new InvalidArgumentException('Unable to parse URI: "'. $uri . '"');
        }

        $instance = new self;
        $instance->scheme   = isset($parts['scheme'])   ? $instance->normalizeScheme($parts['scheme']) : '';
        $instance->host     = isset($parts['host'])     ? $instance->normalizeHost($parts['host']) : '';
        $instance->port     = isset($parts['port'])     ? $instance->normalizePort($parts['port']) : null;
        $instance->path     = isset($parts['path'])     ? $instance->normalizePath($parts['path']) : '';
        $instance->query    = isset($parts['query'])    ? $instance->normalizeQuery($parts['query']) : '';
        $instance->fragment = isset($parts['fragment']) ? $instance->normalizeFragment($parts['fragment']) : '';

        $instance->userInfo = (string) ($parts['user'] ?? '');
        if (isset($parts['pass'])) {
            $instance->userInfo .= ':' . ((string) $parts['pass']);
        }

        return $instance;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->authority === null) {
            if ($this->host === '') {
                return $this->authority = '';
            } else {
                $standardPort = self::STANDARD_PORTS[$this->scheme] ?? null;
                if ($this->port && $this->port !== $standardPort) {
                    if ($this->userInfo) {
                        $this->authority = $this->userInfo . '@' . $this->host . ':' . $this->port;
                    } else {
                        $this->authority = $this->host . ':' . $this->port;
                    }
                } else {
                    if ($this->userInfo) {
                        $this->authority = $this->userInfo . '@' . $this->host;
                    } else {
                        $this->authority = $this->host;
                    }
                }
            }
        }
        return $this->authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        $standardPort = self::STANDARD_PORTS[$this->scheme] ?? null;
        if ($this->port === $standardPort) {
            return null;
        }
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     * @return static
     * @throws InvalidArgumentException
     */
    public function withScheme($scheme): self
    {
        if ($scheme === $this->scheme) {
            return $this;
        }

        $scheme = $this->normalizeScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }

        $copy = clone $this;
        $copy->scheme = $scheme;
        $copy->authority = null;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param string $user
     * @param null|string $password
     * @return static
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = $this->normalizeUserInfo($user, $password);
        if ($this->userInfo === $userInfo) {
            return $this;
        }

        $copy = clone $this;
        $copy->userInfo = $userInfo;
        $copy->authority = null;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param string $host
     * @return static
     * @throws InvalidArgumentException
     */
    public function withHost($host): self
    {
        if ($this->host === $host) {
            return $this;
        }

        $normalizedHost = $this->normalizeHost($host);
        if ($this->host === $normalizedHost) {
            return $this;
        }

        $copy = clone $this;
        $copy->host = $normalizedHost;
        $copy->authority = null;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param int|null $port
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPort($port)
    {
        if ($this->port === $port) {
            return $this;
        }

        $normalizedPort = $this->normalizePort($port);
        if ($this->port === $normalizedPort) {
            return $this;
        }

        $copy = clone $this;
        $copy->port = $normalizedPort;
        $copy->authority = null;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param string $path
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPath($path)
    {
        if ($this->path === $path) {
            return $this;
        }

        $normalizedPath = $this->normalizePath($path);
        if ($this->path === $normalizedPath) {
            return $this;
        }

        $copy = clone $this;
        $copy->path = $normalizedPath;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param string $query
     * @return static
     * @throws InvalidArgumentException
     */
    public function withQuery($query)
    {
        if ($this->query === $query) {
            return $this;
        }

        $normalizedQuery = $this->normalizeQuery($query);
        if ($this->query === $normalizedQuery) {
            return $this;
        }

        $copy = clone $this;
        $copy->query = $normalizedQuery;
        $copy->literal = null;
        return $copy;
    }

    /**
     * @param string $fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        if ($this->fragment === $fragment) {
            return $this;
        }

        $normalizedFragment = $this->normalizeFragment($fragment);
        if ($this->fragment === $normalizedFragment) {
            return $this;
        }

        $copy = clone $this;
        $copy->fragment = $normalizedFragment;
        $copy->literal = null;
        return $copy;
    }

    public function __toString(): string
    {
        if (!isset($this->literal)) {
            $literal = '';
            if ($this->scheme !== '') {
                $literal .= $this->scheme . ':';
            }

            $authority = $this->getAuthority();
            if ($authority !== '') {
                $literal .= '//' . $authority;
            }

            if ($this->path !== '') {
                $path = $this->path;
                if ($path[0] !== '/') {
                    if ($authority !== '') {
                        $path = '/' . $path;
                    }
                } elseif (isset($path[1]) && $path[1] === '/') {
                    if ($authority === '') {
                        $path = '/' . ltrim($path, '/');
                    }
                }
                $literal .= $path;
            }
            if ($this->query !== '') {
                $literal .= '?' . $this->query;
            }
            if ($this->fragment !== '') {
                $literal .= '#' . $this->fragment;
            }

            $this->literal = $literal;
        }
        return $this->literal;
    }
}
