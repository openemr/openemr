<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Value;

/**
 * This class is used to wrap both input as output arrays.
 *
 * @package Particle\Validator
 */
class Container
{
    /**
     * Contains the values (either input or output).
     *
     * @var array
     */
    protected $values = [];

    /**
     * Construct the Value\Container.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Determines whether or not the container has a value for key $key.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->traverse($key, false);
    }

    /**
     * Returns the value for the key $key, or null if the value doesn't exist.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->traverse($key, true);
    }

    /**
     * Set the value of $key to $value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (strpos($key, '.') !== false) {
            return $this->setTraverse($key, $value);
        }
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * Returns a plain array representation of the Value\Container object.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->values;
    }

    /**
     * Traverses the key using dot notation. Based on the second parameter, it will return the value or if it was set.
     *
     * @param string $key
     * @param bool $returnValue
     * @return mixed
     */
    protected function traverse($key, $returnValue = true)
    {
        $value = $this->values;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return false;
            }
            $value = $value[$part];
        }
        return $returnValue ? $value : true;
    }

    /**
     * Uses dot-notation to set a value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    protected function setTraverse($key, $value)
    {
        $parts = explode('.', $key);
        $ref = &$this->values;

        foreach ($parts as $i => $part) {
            if ($i < count($parts) - 1 && (!isset($ref[$part]) || !is_array($ref[$part]))) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }

        $ref = $value;
        return $this;
    }
}
