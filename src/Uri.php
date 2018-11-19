<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\UriValidatorTrait;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri extends Chain implements UriInterface
{
    private const STANDARD_PORTS = [
        'http'  =>  80,
        'https' => 443
    ];

    use UriValidatorTrait;

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
        $instance = new static;
        $constructor = function (array $properties, array $generators) use ($instance): Uri {
            $instance->properties = $properties;
            $instance->generators = $generators;
            return $instance;
        };
        return new class($constructor, $validate) extends UriBuilder {};
    }

    /**
     * @param string $uri
     * @return Uri
     */
    public static function parse($uri): Uri
    {
        if (!\is_string($uri)) {
            throw new InvalidArgumentException('URI must be a string');
        }
        $parts = \parse_url($uri);
        if ($parts === false) {
            throw new InvalidArgumentException('Unable to parse URI: "'. $uri . '"');
        }

        $instance = new self;
        $instance->properties = [
            'scheme'    => isset($parts['scheme'])   ? $instance->normalizeScheme($parts['scheme']) : '',
            'host'     => isset($parts['host'])     ? $instance->normalizeHost($parts['host']) : '',
            'port'     => isset($parts['port'])     ? $instance->normalizePort($parts['port']) : null,
            'path'     => isset($parts['path'])     ? $instance->normalizePath($parts['path']) : '',
            'query'    => isset($parts['query'])    ? $instance->normalizeQuery($parts['query']) : '',
            'fragment' => isset($parts['fragment']) ? $instance->normalizeFragment($parts['fragment']) : ''
        ];

        $userInfo = $parts['user'] ?? '';
        if (isset($parts['pass'])) {
            $userInfo .= ':' . $parts['pass'];
        }
        $instance->properties['userInfo'] = $userInfo;
        return $instance;
    }

    public function getScheme(): string
    {
        return $this->fetch('scheme', '');
    }

    public function getAuthority(): string
    {
        if (\array_key_exists('authority', $this->properties)) {
            return $this->properties['authority'];
        }

        $host = $this->getHost();

        if ($host === '') {
            $authority = '';
        } else {
            $port = $this->getPort();
            $userInfo = $this->getUserInfo();
            if ($port) {
                if ($userInfo) {
                    $authority = $userInfo . '@' . $host . ':' . $port;
                } else {
                    $authority = $host . ':' . $port;
                }
            } else {
                if ($userInfo) {
                    $authority = $userInfo . '@' . $host;
                } else {
                    $authority = $host;
                }
            }
        }

        return $this->properties['authority'] = $authority;
    }

    public function getUserInfo(): string
    {
        return $this->fetch('userInfo', '');
    }

    public function getHost(): string
    {
        return $this->fetch('host', '');
    }

    public function getPort(): ?int
    {
        if (\array_key_exists('filteredPort', $this->properties)) {
            return $this->properties['filteredPort'];
        }

        $standardPort = self::STANDARD_PORTS[$this->fetch('scheme')] ?? null;
        $port = $this->fetch('port');

        return $this->properties['filteredPort'] = ($port === $standardPort) ? null : $port;
    }

    public function getPath(): string
    {
        return $this->fetch('path', null);
    }

    public function getQuery(): string
    {
        return $this->fetch('query', '');
    }

    public function getFragment(): string
    {
        return $this->fetch('fragment', '');
    }

    /**
     * @param string $scheme
     * @return static
     * @throws InvalidArgumentException
     */
    public function withScheme($scheme): self
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['scheme'] = [[&$this, 'normalizeScheme'], $scheme];
        return $uri;
    }

    /**
     * @param string $user
     * @param null|string $password
     * @return static
     */
    public function withUserInfo($user, $password = null)
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['userInfo'] = [[&$this, 'normalizeUserInfo'], &$user, $password];
        return $uri;
    }

    /**
     * @param string $host
     * @return static
     * @throws InvalidArgumentException
     */
    public function withHost($host): self
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['host'] = [[&$this, 'normalizeHost'], &$host];
        return $uri;
    }

    /**
     * @param int|null $port
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPort($port)
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['port'] = [[&$this, 'normalizePort'], &$port];
        return $uri;
    }

    /**
     * @param string $path
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPath($path)
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['path'] = [[&$this, 'normalizePath'], &$path];
        return $uri;
    }

    /**
     * @param string $query
     * @return static
     * @throws InvalidArgumentException
     */
    public function withQuery($query)
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['query'] = [[&$this, 'normalizeQuery'], &$query];
        return $uri;
    }

    /**
     * @param string $fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $uri = new static;
        $uri->parent = &$this;
        $uri->generators['fragment'] = [[&$this, 'normalizeFragment'], &$fragment];
        return $uri;
    }

    public function __toString(): string
    {
        if (\array_key_exists('literal', $this->properties)) {
            return $this->properties['literal'];
        }

        $literal = '';
        $scheme = $this->getScheme();

        if ($scheme !== '') {
            $literal .= $scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $literal .= '//' . $authority;
        }

        $path = $this->getPath();
        if ($path !== '') {
            if ($path[0] !== '/') {
                if ($authority !== '') {
                    $path = '/' . $path;
                }
            } elseif (isset($path[1]) && $path[1] === '/') {
                if ($authority === '') {
                    $path = '/' . \ltrim($path, '/');
                }
            }
            $literal .= $path;
        }

        $query = $this->getQuery();
        if ($query !== '') {
            $literal .= '?' . $query;
        }

        $fragment = $this->getFragment();
        if ($fragment !== '') {
            $literal .= '#' . $fragment;
        }

        return $this->properties['literal'] = $literal;
    }
}
