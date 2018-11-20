<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\MessageValidatorTrait;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    use MessageValidatorTrait;

    /**
     * @var string|null
     */
    protected $protocolVersion = null;

    /**
     * @var callable|null
     * @psalm-var callable():string|null
     */
    protected $protocolVersionGenerator = null;

    /**
     * @var array|null
     * @var array<string,array<int,string>>|null
     */
    protected $headers = null;

    /**
     * @var array|null
     * @psalm-var array<string,string>|null
     */
    protected $headerNames = null;

    /**
     * @var callable|null
     * @psalm-var callable():array<string,array<string>>|null
     */
    protected $headersGenerator = null;

    /**
     * @var StreamInterface|null
     */
    protected $body = null;

    /**
     * @var callable|null
     * @psalm-var callable():StreamInterface|null
     */
    protected $bodyGenerator = null;

    protected function __construct()
    {
    }

    /**
     * @return static
     */
    public static function empty()
    {
        return new static;
    }

    /**
     * @return MessageBuilder
     */
    public static function validatingBuilder()
    {
        $instance = new Message;
        $constructor = function (
            ?string $protocolVersion,
            ?array $headers,
            ?StreamInterface $body
        ) use ($instance): Message {
            $instance->protocolVersion = $protocolVersion;
            $instance->headers = $headers;
            $instance->body = $body;
            return $instance;
        };
        return new class($constructor, true) extends MessageBuilder {};
    }

    /**
     * @return MessageBuilder
     */
    public static function nonValidatingBuilder()
    {
        $instance = new Message;
        $constructor = function (
            ?string $protocolVersion,
            ?array $headers,
            ?StreamInterface $body
        ) use ($instance): Message {
            $instance->protocolVersion = $protocolVersion;
            $instance->headers = $headers;
            $instance->body = $body;
            return $instance;
        };
        return new class($constructor, false) extends MessageBuilder {};
    }

    public function getProtocolVersion(): string
    {
        if (!isset($this->protocolVersion)) {
            if (isset($this->protocolVersionGenerator)) {
                $this->protocolVersion = ($this->protocolVersionGenerator)();
                $this->protocolVersionGenerator = null;
            } else {
                $this->protocolVersion = '';
            }
        }
        return $this->protocolVersion;
    }

    /**
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $copy = clone $this;
        $copy->protocolVersion = $this->validateProtocolVersion($version);
        $copy->protocolVersionGenerator = null;
        return $copy;
    }

    /**
     * @return string[][]
     * @psalm-return array<string,array<int,string>>
     */
    public function getHeaders(): array
    {
        if (!isset($this->headers)) {
            if (isset($this->headersGenerator)) {
                $this->headers = ($this->headersGenerator)();
                $this->headersGenerator = null;
            } else {
                $this->headers = [];
            }
        }
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        $headers = $this->getHeaders();
        $normalizedName = $this->normalizeHeaderName($name);
        return isset($headers[$normalizedName]);
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getHeader($name): array
    {
        $headers = $this->getHeaders();
        $normalizedName = $this->normalizeHeaderName($name);
        return $headers[$normalizedName] ?? [];
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
        $normalizedName = $this->normalizeHeaderName($name);
        $normalizedValue = $this->validateHeaderValue($normalizedName, $value);

        $copy = clone $this;
        if ($normalizedName === 'Host') {
            $headers = $copy->getHeaders();
            $copy->headers = ['Host' => $normalizedValue] + $headers;
        } else {
            $copy->headers[$normalizedName] = $normalizedValue;
        }
        $copy->headerNames[\strtolower($normalizedName)] = $normalizedName;
        return $copy;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     * @throws InvalidArgumentException
     */
    public function withAddedHeader($name, $value)
    {
        $normalizedName = $this->normalizeHeaderName($name);
        $normalizedValue = $this->validateHeaderValue($normalizedName, $value);

        $copy = clone $this;
        if (isset($copy->headers[$normalizedName]) && $normalizedName !== 'Host') {
            $copy->headers[$normalizedName] = array_merge($copy->headers[$normalizedName], $normalizedValue);
        } else {
            $copy->headers[$normalizedName] = $normalizedValue;
            $copy->headerNames[\strtolower($normalizedName)] = $normalizedName;
        }
        return $copy;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $headers = $this->getHeaders();
        $normalizedName = $this->normalizeHeaderName($name);

        if (!isset($headers[$normalizedName])) {
            return $this;
        }

        $copy = clone $this;
        unset($copy->headers[$normalizedName]);
        return $copy;
    }

    public function getBody(): StreamInterface
    {
        if (!isset($this->body)) {
            if (isset($this->bodyGenerator)) {
                $this->body = ($this->bodyGenerator)();
                $this->bodyGenerator = null;
            } else {
                $this->body = Stream::empty();
            }
        }
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     * @return static
     * @throws InvalidArgumentException
     */
    public function withBody(StreamInterface $body)
    {
        $copy = clone $this;
        $copy->body = $this->validateBody($body);
        $copy->bodyGenerator = null;
        return $copy;
    }

    protected function normalizeHeaderName($name)
    {
        if (!isset($this->headerNames)) {
            $this->headerNames = [];
            foreach ($this->getHeaders() as $n => &$_) {
                $k = \strtolower($n);
                if (!isset($this->headerNames[$k])) {
                    $this->headerNames[$k] = $n;
                }
            }
        }
        $this->validateHeaderName($name);
        $key = \strtolower($name);
        if ($key === 'host') {
            return 'Host';
        }
        if (isset($this->headerNames[$key])) {
            return $this->headerNames[$key];
        }
        return $name;
    }
}
