<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\MessageBuilderTrait;

abstract class MessageBuilder
{
    use MessageBuilderTrait;

    /**
     * @var callable
     */
    protected $constructor;

    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    public function build(): Message
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body);
    }
}
