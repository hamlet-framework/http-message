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

    /** @var array */
    protected $properties = [];

    /** @var array */
    protected $generators = [];

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
        $this->properties['protocolVersion'] = $this->validate ? $this->validateProtocolVersion($version) : $version;
        return $this;
    }

    /**
     * @param array $headers
     * @return static
     */
    public function withHeaders(array $headers)
    {
        if ($this->validate) {
            $names = [];
            $values = [];
            foreach ($headers as $name => &$value) {
                $key = \strtolower($name);
                if (!isset($names[$key])) {
                    $names[$key] = $key == 'host' ? 'Host' : $name;
                }
                $normalizedName = $names[$key];
                $normalizedValue = $this->validateAndNormalizeHeader($name, $value);
                if (\array_key_exists($normalizedName, $values)) {
                    $values[$normalizedName] = array_merge($values[$normalizedName], $normalizedValue);
                } else {
                    $values[$normalizedName] = $normalizedValue;
                }
            }
            if (\array_key_exists('host', $names)) {
                reset($values);
                if (key($values) != 'Host') {
                    $host = $values['Host'];
                    unset($values['Host']);
                    $values = ['Host' => $host] + $values;
                }
            }
            $this->properties['headers'] = [$values, $names];
        } else {
            $this->properties['headers'] = [$headers, null];
        }
        return $this;
    }

    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $this->properties['body'] = $this->validate ? $this->validateBody($body) : $body;
        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function build()
    {
        return ($this->constructor)($this->properties, $this->generators);
    }
}
