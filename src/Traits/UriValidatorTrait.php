<?php declare(strict_types=1);

namespace Hamlet\Http\Message\Traits;

use InvalidArgumentException;

trait UriValidatorTrait
{
    /**
     * @param mixed $scheme
     * @return string
     * @see https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
     */
    protected function normalizeScheme($scheme): string
    {
        if (!\is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }
        if (!\preg_match('/^[a-zA-Z][-a-zA-Z0-9.+]*$/', $scheme)) {
            throw new InvalidArgumentException('Invalid scheme format "' . $scheme . '"');
        }
        return \strtolower($scheme);
    }

    /**
     * @param mixed $host
     * @return string
     */
    protected function normalizeHost($host): string
    {
        if (!\is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }
        return \strtolower($host);
    }

    /**
     * @param mixed $port
     * @return int|null
     */
    protected function normalizePort($port): ?int
    {
        if ($port === null) {
            return null;
        }
        if (!\is_int($port)) {
            throw new InvalidArgumentException('Port must be an integer');
        }
        if ($port < 1 || 0xffff < $port) {
            throw new InvalidArgumentException('Invalid port: ' . $port .  '. Must be between 1 and 65535', $port);
        }
        return $port;
    }

    /**
     * @param mixed $path
     * @return string
     */
    protected function normalizePath($path): string
    {
        if (!\is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }
        return $this->escape($path, false);
    }

    /**
     * @param mixed $query
     * @return string
     */
    protected function normalizeQuery($query): string
    {
        if (!\is_string($query)) {
            throw new InvalidArgumentException('Query must be a string');
        }
        return $this->escape($query, true);
    }

    /**
     * @param mixed $fragment
     * @return string
     */
    protected function normalizeFragment($fragment): string
    {
        if (!\is_string($fragment)) {
            throw new InvalidArgumentException('Fragment must be a string');
        }
        return $this->escape($fragment, true);
    }

    protected function escape(string $string, bool $acceptQuestion): string
    {
        $valid = '-a-zA-Z0-9_.~!$&\'()*+,;=%:@/';
        if ($acceptQuestion) {
            $valid .= '?';
        }
        $pattern = '#([^' . $valid . ']+|%(?![A-Fa-f0-9]{2}))#';
        $callback = function (array $match): string {
            return \rawurlencode($match[0]);
        };
        $result = \preg_replace_callback($pattern, $callback, $string);
        if ($result === null) {
            throw new InvalidArgumentException('Cannot escape string');
        }
        return $result;
    }

    /**
     * @param mixed $name
     * @param mixed $password
     * @return string
     */
    protected function normalizeUserInfo($name, $password): string
    {
        if (!\is_string($name)) {
            throw new InvalidArgumentException('User name must be a string');
        }
        if ($password !== null && !\is_string($password)) {
            throw new InvalidArgumentException('Password must be a string');
        }
        return $password === null || $password === '' ? $name : $name . ':' . $password;
    }
}
