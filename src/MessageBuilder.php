<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Traits\MessageValidatorTrait;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class MessageBuilder
{
    use MessageValidatorTrait;

    /** @var callable */
    protected $constructor;

    /** @var bool */
    protected $validate;

    /** @var string|null */
    protected $protocolVersion = null;

    /** @var array|null */
    protected $headers = null;

    /** @var StreamInterface|null */
    protected $body = null;

    public function __construct(callable $constructor, bool $validate)
    {
        $this->constructor = $constructor;
        $this->validate = $validate;
    }

    /**
     * @param string $version
     * @return static
     */
    public function withProtocolVersion(string $version)
    {
        $this->protocolVersion = $this->validate ? $this->validateProtocolVersion($version) : $version;
        return $this;
    }

    /**
     * @param array $headers
     * @return static
     */
    public function withHeaders(array $headers)
    {
        if ($this->validate) {
            $values = [];
            $names = [];
            foreach ($headers as $name => &$value) {
                $key = \strtolower($name);
                if ($key === 'host') {
                    $normalizedName = 'Host';
                } elseif (isset($names[$key])) {
                    $normalizedName = $names[$key];
                } else {
                    $normalizedName = $name;
                    $names[$key] = $name;
                }
                $normalizedValue = $this->validateHeaderValue($normalizedName, $value);
                if (\array_key_exists($normalizedName, $values)) {
                    $values[$normalizedName] = array_merge($values[$normalizedName], $normalizedValue);
                } else {
                    $values[$normalizedName] = $normalizedValue;
                }
            }
            if (isset($values['Host'])) {
                reset($values);
                if (key($values) != 'Host') {
                    $host = $values['Host'];
                    unset($values['Host']);
                    $values = ['Host' => $host] + $values;
                }
            }
            $this->headers = $values;
        } else {
            $this->headers = $headers;
        }
        return $this;
    }

    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $this->validate ? $this->validateBody($body) : $body;
        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function build()
    {
        return ($this->constructor)($this->protocolVersion, $this->headers, $this->body);
    }
}
