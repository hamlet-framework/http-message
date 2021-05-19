<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\StreamInterface;

/**
 * @psalm-import-type Headers from Message
 */
abstract class MessageBuilder
{
    use MessageBuilderTrait;

    /**
     * @var callable(string|null,Headers|null,StreamInterface|null):Message
     */
    protected $constructor;

    /**
     * @param callable(string|null,Headers|null,StreamInterface|null):Message $constructor
     * @param bool $validate
     */
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
