<?php declare(strict_types=1);

namespace Hamlet\Http\Message\Traits;

use Psr\Http\Message\StreamInterface;
use function array_key_exists;
use function strtolower;

trait MessageBuilderTrait
{
    use MessageValidatorTrait;

    /**
     * @var bool
     */
    protected $validate;

    /**
     * @var string|null
     */
    protected $protocolVersion = null;

    /**
     * @var array|null
     * @psalm-var array<string,array<string>>|null
     */
    protected $headers = null;

    /**
     * @var StreamInterface|null
     */
    protected $body = null;

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
     * @psalm-param array<string,string|array<string>> $headers
     * @return static
     */
    public function withHeaders(array $headers)
    {
        if ($this->validate) {
            $values = [];
            $names = [];
            foreach ($headers as $name => &$value) {
                $name = $this->validateHeaderName($name);
                $key = strtolower($name);
                if ($key === 'host') {
                    $normalizedName = 'Host';
                } elseif (isset($names[$key])) {
                    $normalizedName = $names[$key];
                } else {
                    $normalizedName = $name;
                    $names[$key] = $name;
                }
                $normalizedValue = $this->validateHeaderValue($normalizedName, $value);
                if (array_key_exists($normalizedName, $values)) {
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
            /**
             * @psalm-suppress MixedTypeCoercion
             * @psalm-suppress MixedPropertyTypeCoercion
             */
            $this->headers = $values;
        } else {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
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
}
