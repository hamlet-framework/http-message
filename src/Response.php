<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    /**
     * @param bool $validate
     * @return ResponseBuilder
     */
    protected static function builder(bool $validate)
    {
        $instance = new static;
        $constructor = function (array &$properties, array &$generators) use ($instance) {
            $instance->properties = $properties;
            $instance->generators = $generators;
            return $instance;
        };
        return new class($constructor, $validate) extends ResponseBuilder {};
    }

    /**
     * @return ResponseBuilder
     */
    public static function validatingBuilder()
    {
        return self::builder(true);
    }

    /**
     * @return ResponseBuilder
     */
    public static function nonValidatingBuilder()
    {
        return self::builder(false);
    }

    public function getProtocolVersion(): string
    {
        return (string) $this->fetch('protocolVersion', '1.1');
    }

    public function getStatusCode(): int
    {
        list($code) = $this->fetch('status', [200, 'OK']);
        return $code;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return static
     * @throws InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $response = new static;
        $response->parent = &$this;
        $response->properties['status'] = $this->validateAndNormalizeStatusCodeAndReasonPhrase($code, $reasonPhrase);
        return $response;
    }

    public function getReasonPhrase(): string
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($_, $reason) = $this->fetch('status', [200, 'OK']);
        return $reason;
    }
}
