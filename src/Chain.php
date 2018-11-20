<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Closure;

abstract class Chain
{
    /** @var static|null */
    protected $parent = null;

    /** @var array */
    protected $properties = [];

    /** @var array */
    protected $generators = [];

    protected function __construct()
    {
    }

    /**
     * @return static
     */
    public static function empty()
    {
        return new static;
    }

    protected static function constructor(): callable
    {
        $instance = new static;
        $constructor =
            /**
             * @param array $properties
             * @param array $generators
             * @return static
             */
            function (array &$properties, array &$generators) use ($instance) {
                $instance->properties = $properties;
                $instance->generators = $generators;
                return $instance;
            };
        return $constructor;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function &fetch(string $name, $default = null)
    {
        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        if (\array_key_exists($name, $this->generators)) {
            $this->properties[$name] = call_user_func(...$this->generators[$name]);
            return $this->properties[$name];
        }
        if ($this->parent !== null) {
            $this->properties[$name] = &$this->parent->fetch($name, $default);
            return $this->properties[$name];
        }
        $this->properties[$name] = $default;
        return $default;
    }
}
