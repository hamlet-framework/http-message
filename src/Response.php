<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @psalm-import-type Headers from Message
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var int|null
     */
    protected $statusCode = null;

    /**
     * @var (callable():int)|null
     */
    protected $statusCodeGenerator = null;

    /**
     * @var string|null
     */
    protected $reasonPhrase = null;

    /**
     * @return callable(string|null,Headers|null,StreamInterface|null,int|null,string|null):self
     */
    private static function responseConstructor(): callable
    {
        $instance = new Response;
        return
            /**
             * @param string|null $protocolVersion
             * @param Headers|null $headers
             * @param StreamInterface|null $body
             * @param int|null $statusCode
             * @param string|null $reasonPhrase
             * @return self
             */
            function ($protocolVersion, $headers, $body, $statusCode, $reasonPhrase) use ($instance): Response {
                $instance->protocolVersion = $protocolVersion;
                $instance->headers = $headers;
                $instance->body = $body;
                $instance->statusCode = $statusCode;
                $instance->reasonPhrase = $reasonPhrase;
                return $instance;
            };
    }

    /**
     * @return ResponseBuilder
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public static function validatingBuilder()
    {
        $constructor = self::responseConstructor();
        return new class($constructor, true) extends ResponseBuilder {
        };
    }

    /**
     * @return ResponseBuilder
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public static function nonValidatingBuilder()
    {
        $constructor = self::responseConstructor();
        return new class($constructor, false) extends ResponseBuilder {
        };
    }

    public function getProtocolVersion(): string
    {
        if (!isset($this->protocolVersion)) {
            if (isset($this->protocolVersionGenerator)) {
                $this->protocolVersion = ($this->protocolVersionGenerator)();
                $this->protocolVersionGenerator = null;
            } else {
                $this->protocolVersion = '1.1';
            }
        }
        return $this->protocolVersion;
    }

    public function getStatusCode(): int
    {
        if (!isset($this->statusCode)) {
            if (isset($this->statusCodeGenerator)) {
                $this->statusCode = ($this->statusCodeGenerator)();
                $this->statusCodeGenerator = null;
            } else {
                $this->statusCode = 200;
            }
        }
        return $this->statusCode;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return static
     * @throws InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if ($this->getStatusCode() === $code && $this->getReasonPhrase() === $reasonPhrase) {
            return $this;
        }

        list($normalizedStatusCode, $normalizedReasonPhrase) = $this->validateAndNormalizeStatusCodeAndReasonPhrase($code, $reasonPhrase);
        $copy = clone $this;
        $copy->statusCode = $normalizedStatusCode;
        $copy->reasonPhrase = $normalizedReasonPhrase;
        $copy->statusCodeGenerator = null;
        return $copy;
    }

    public function getReasonPhrase(): string
    {
        if (!isset($this->reasonPhrase)) {
            $statusCode = $this->getStatusCode();
            $this->reasonPhrase = self::$REASON_PHRASES[$statusCode] ?? '';
        }
        return $this->reasonPhrase;
    }
}
