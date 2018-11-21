<?php

namespace Hamlet\Http\Message\Traits;

trait ResponseBuilderTrait
{
    use MessageBuilderTrait;

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
}
