<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\MessageValidatorTrait;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Message extends Chain implements MessageInterface
{
    use MessageValidatorTrait;

    /**
     * @return MessageBuilder
     */
    public static function validatingBuilder()
    {
        return new class(self::constructor(), true) extends MessageBuilder {};
    }

    /**
     * @return MessageBuilder
     */
    public static function nonValidatingBuilder()
    {
        return new class(self::constructor(), false) extends MessageBuilder {};
    }

    public function getProtocolVersion(): string
    {
        return $this->fetch('protocolVersion', '');
    }

    /**
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $message = new static;
        $message->parent = &$this;
        $message->generators['protocolVersion'] = [[&$this, 'validateProtocolVersion'], &$version];
        return $message;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        list($values) = $this->enhancedHeaders();
        return $values;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($_, $names) = $this->enhancedHeaders();
        return \array_key_exists(\strtolower($name), (array) $names);
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getHeader($name): array
    {
        list($values, $names) = $this->enhancedHeaders();
        if (!empty($names)) {
            $key = \strtolower($name);
            if (\array_key_exists($key, $names)) {
                return $values[$names[$key]];
            }
        }
        return [];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return join(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     * @throws InvalidArgumentException
     */
    public function withHeader($name, $value)
    {
        $message = new static;
        $message->parent = &$this;
        $message->generators['headers'] = [[&$this, 'replaceHeader'], &$name, &$value];
        return $message;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     * @throws InvalidArgumentException
     */
    public function withAddedHeader($name, $value)
    {
        $message = new static;
        $message->parent = &$this;
        $message->generators['headers'] = [[&$this, 'extendHeader'], &$name, &$value];
        return $message;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $message = new static;
        $message->parent = &$this;
        $message->generators['headers'] = [[&$this, 'removeHeader'], &$name];
        return $message;
    }

    public function getBody(): StreamInterface
    {
        $body = $this->fetch('body', null);
        if ($body !== null) {
            return $body;
        }
        return $this->properties['body'] = Stream::empty();
    }

    /**
     * @param StreamInterface $body
     * @return static
     * @throws InvalidArgumentException
     */
    public function withBody(StreamInterface $body)
    {
        $message = new static;
        $message->parent = &$this;
        $message->generators['body'] = [[&$this, 'validateBody'], &$body];
        return $message;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return array
     */
    protected function replaceHeader($name, $value): array
    {
        list($values, $names) = $this->headers();
        $normalizedValue = $this->validateAndNormalizeHeader($name, $value);
        $key = \strtolower($name);
        if (!isset($names[$key])) {
            $names[$key] = $key == 'host' ? 'Host' : $name;
        }
        if ($key == 'host') {
            $values = ['Host' => $normalizedValue] + $values;
        } else {
            $values[$names[$key]] = $normalizedValue;
        }
        return [$values, $names];
    }

    /**
     * @param mixed $name
     * @return array
     */
    protected function removeHeader($name): array
    {
        list($values, $names) = $this->headers();
        $key = \strtolower($name);
        if (\array_key_exists($key, $names)) {
            unset($values[$names[$key]]);
            unset($names[$key]);
        }
        return [$values, $names];
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return array
     */
    protected function extendHeader($name, $value): array
    {
        list($values, $names) = $this->headers();
        $normalizedValue = $this->validateAndNormalizeHeader($name, $value);
        $key = \strtolower($name);
        if (!isset($names[$key])) {
            $names[$key] = $key == 'host' ? 'Host' : $name;
        }
        $normalizedName = $names[$key];
        if ($key == 'host') {
            $values = ['Host' => $normalizedValue] + $values;
        } elseif (isset($values[$normalizedName])) {
            $values[$normalizedName] = array_merge($values[$normalizedName], $normalizedValue);
        } else {
            $values[$normalizedName] = $normalizedValue;
        }
        return [$values, $names];
    }

    protected function headers(): array
    {
        list($values, $names) = $this->fetch('headers', [[], []]);
        if ($names === null) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($values as $name => &$_) {
                $names[\strtolower($name)] = $name;
            }
            $this->properties['headers'] = [$values, $names];
        }
        return [$values, $names];
    }

    protected function enhancedHeaders(): array
    {
        if (\array_key_exists('enhancedHeaders', $this->properties)) {
            return $this->properties['enhancedHeaders'];
        }

        list($values, $names) = $this->headers();
        if (\array_key_exists('host', $names)) {
            return $this->properties['enhancedHeaders'] = [$values, $names];
        }

        /** @var UriInterface|null $uri */
        $uri = $this->fetch('uri');
        if ($uri === null) {
            return $this->properties['enhancedHeaders'] = [$values, $names];
        }

        $host = $uri->getHost();
        if (empty($host)) {
            return $this->properties['enhancedHeaders'] = [$values, $names];
        }

        $port = $uri->getPort();
        if ($port) {
            $value = $host . ':' . $port;
        } else {
            $value = $host;
        }

        if (\array_key_exists('host', $names)) {
            $values['Host'] = [$value];
        } else {
            $values = ['Host' => [$value]] + $values;
            $names['host'] = 'Host';
        }
        return $this->properties['enhancedHeaders'] = [$values, $names];
    }
}
