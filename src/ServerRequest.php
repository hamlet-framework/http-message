<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @param bool $validate
     * @return ServerRequestBuilder
     */
    protected static function builder(bool $validate)
    {
        $instance = new static;
        $constructor = function (array &$properties, array &$generators) use ($instance) {
            $instance->properties = $properties;
            $instance->generators = $generators;
            return $instance;
        };
        return new class($constructor, $validate) extends ServerRequestBuilder {};
    }

    /**
     * @return ServerRequestBuilder
     */
    public static function validatingBuilder()
    {
        return self::builder(true);
    }

    /**
     * @return ServerRequestBuilder
     */
    public static function nonValidatingBuilder()
    {
        return self::builder(false);
    }

    public function getServerParams(): array
    {
        return $this->fetch('serverParams', []);
    }

    public function getCookieParams(): array
    {
        return $this->fetch('cookieParams', []);
    }

    /**
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['cookieParams'] = [[&$this, 'validateCookieParams'], &$cookies];
        return $request;
    }

    public function getQueryParams(): array
    {
        return $this->fetch('queryParams', []);
    }

    /**
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['queryParams'] = [[&$this, 'validateQueryParams'], &$query];
        return $request;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->fetch('uploadedFiles', []);
    }

    /**
     * @param array $uploadedFiles
     * @return static
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['uploadedFiles'] = [[&$this, 'validateUploadedFiles'], &$uploadedFiles];
        return $request;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->fetch('parsedBody');
    }

    /**
     * @param array|object|null $data
     * @return static
     * @throws InvalidArgumentException
     */
    public function withParsedBody($data)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['parsedBody'] = [[&$this, 'validateParsedBody'], &$data];
        return $request;
    }

    public function getAttributes(): array
    {
        return (array) $this->fetch('attributes', []);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $attributes = (array) $this->fetch('attributes', []);
        if (\array_key_exists($name, $attributes)) {
            return $attributes[$name];
        }
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['attributes'] = [[&$this, 'addAttribute'], &$name, &$value];
        return $request;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        $request = new static;
        $request->parent = &$this;
        $request->generators['attributes'] = [[&$this, 'removeAttribute'], &$name];
        return $request;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return array
     */
    protected function addAttribute($name, $value)
    {
        if (!\is_string($name)) {
            throw new \InvalidArgumentException('Attribute name must be a string');
        }
        $attributes = (array) $this->fetch('attributes', []);
        $attributes[$name] = $value;
        return $attributes;
    }

    /**
     * @param mixed $name
     * @return array
     */
    protected function removeAttribute($name)
    {
        if (!\is_string($name)) {
            throw new \InvalidArgumentException('Attribute name must be a string');
        }
        $attributes = (array) $this->fetch('attributes', []);
        if (\array_key_exists($name, $attributes)) {
            unset($attributes[$name]);
        }
        return $attributes;
    }
}
