<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    /**
     * @return ResponseBuilder
     */
    public static function validatingBuilder()
    {
        return new class(self::constructor(), true) extends ResponseBuilder {};
    }

    /**
     * @return ResponseBuilder
     */
    public static function nonValidatingBuilder()
    {
        return new class(self::constructor(), false) extends ResponseBuilder {};
    }

    public function getProtocolVersion(): string
    {
        return $this->fetch('protocolVersion', '1.1');
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
        $response->generators['status'] = [[&$this, 'validateAndNormalizeStatusCodeAndReasonPhrase'], &$code, &$reasonPhrase];
        return $response;
    }

    public function getReasonPhrase(): string
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($_, $reason) = $this->fetch('status', [200, 'OK']);
        return $reason;
    }
}
