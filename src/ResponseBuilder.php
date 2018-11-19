<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\ResponseInterface;

class ResponseBuilder extends MessageBuilder
{
    /**
     * @param int $code
     * @param string $reason
     * @return static
     */
    public function withStatus(int $code, string $reason = '')
    {
        $this->properties['status'] = $this->validate ? $this->validateAndNormalizeStatusCodeAndReasonPhrase($code, $reason) : [$code, $reason];
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function build()
    {
        return ($this->constructor)($this->properties, $this->generators);
    }
}
