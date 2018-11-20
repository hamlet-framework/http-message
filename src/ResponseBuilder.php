<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\ResponseInterface;

class ResponseBuilder extends MessageBuilder
{
    /** @var int|null */
    protected $statusCode;

    /** @var string|null */
    protected $reasonPhrase;

    /**
     * @param int $code
     * @param string $reason
     * @return static
     */
    public function withStatus(int $code, string $reason = '')
    {
        if ($this->validate) {
            list($normalizedStatusCode, $normalizedReasonPhrase) = $this->validateAndNormalizeStatusCodeAndReasonPhrase($code, $reason);
            $this->statusCode = $normalizedStatusCode;
            $this->reasonPhrase = $normalizedReasonPhrase;
        } else {
            $this->statusCode = $code;
            if (!empty($reason)) {
                $this->reasonPhrase = $reason;
            }
        }
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function build()
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body, $this->statusCode, $this->reasonPhrase);
    }
}
