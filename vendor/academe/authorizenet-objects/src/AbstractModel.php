<?php

namespace Academe\AuthorizeNet;

/**
 *
 */

use ReflectionClass;

abstract class AbstractModel implements \JsonSerializable
{
    protected $objectName;
    protected $objectNameSuffix = '';

    public function __construct()
    {
        // If the child class has not defined an object name, then derive
        // it from the class name.

        if (! isset($this->objectName)) {
            $this->objectName = lcfirst(substr(strrchr(get_class($this), '\\'), 1)) . $this->objectNameSuffix;
        }
    }

    /**
     * This is the API data structure object name.
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Magic calling method. Handle the following patterns:
     *
     * - with*($value) - set a value
     * - get*() - return a value
     * - has*() - property exists with a non-null value.
     * - without*() - remove a value (TODO - set a value to null to take it out for now)
     * - values*() - return a list of valid values for a field, or an empty array if none defined.
     * - assertValue*() - assert that the supplied value is one in the permitted list.
     */
    public function __call($name, $arguments)
    {
        // withFoo($value) will clone this object, call setFoo($value) on the clone then return the clone.

        if (substr($name, 0, 4) === 'with') {
            // Get the setter name.

            $setter = 'set' . substr($name, 4);

            // Get the value.
            // Just one value is supported for a with*() method.

            $value = $arguments[0];

            // There must be a setter method to allow this with method to work.
            // All data properties have setters, which is where any validation is performed.

            if (method_exists($this, $setter)) {
                $clone = clone $this;
                $clone->{$setter}($value);
                return $clone;
            }
        } elseif (substr($name, 0, 3) === 'get') {
            // Get the property name.

            $property = lcfirst(substr($name, 3));

            if (property_exists($this, $property)) {
                return $this->{$property};
            }
        } elseif (substr($name, 0, 3) === 'has') {
            // Get the property name.

            $property = lcfirst(substr($name, 3));

            return (property_exists($this, $property) && $this->{$property} !== null);
        } elseif (substr($name, 0, 6) === 'values') {
            // Get the property name.

            $property = lcfirst(substr($name, 6));

            // Convert from initcap CamelCase to capital SNAKE_CASE.
            $prefix = ltrim(strtoupper(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $property)), '_');

            return $this->constantList($prefix);
        } elseif (substr($name, 0, 11) === 'assertValue') {
            $value = isset($arguments[0]) ? $arguments[0] : null;

            if (! isset($value)) {
                return;
            }

            // Get the property name.

            $property = ucfirst(substr($name, 11));

            $values = $this->{'values'.$property}();

            if (! in_array($value, $values)) {
                throw new \InvalidArgumentException(sprintf(
                    'Property "%s" given invalid value "%s"; allowed values are: "%s"',
                    $property,
                    $value,
                    implode('", "', $values)
                ));
            }

            return;
        }

        // We haven't matched any expected method prefixes, so raise a default SPL exception.

        throw new \BadMethodCallException(sprintf('Called method "%s" does not exist', $name));
    }

    /**
     * If a getter exists for a property, then use that in preference,
     * falling back to the underlying property.
     */
    public function __get($property_name)
    {
        $methodName = 'get' . ucfirst($property_name);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        if (property_exists($this, $property_name)) {
            return $this->{$property_name};
        }
    }

    public function with(array $valueList)
    {
        $clone = clone $this;

        foreach ($valueList as $name => $value) {
            $setter = 'set' . ucfirst($name);

            $clone->{$setter}($value);
        }

        return $clone;
    }

    /**
     * Get an array of constants in this [late-bound] class, with an optional prefix.
     * @param null $prefix
     * @return array
     */
    public static function constantList($prefix = null)
    {
        $reflection = new ReflectionClass(get_called_class());
        $constants = $reflection->getConstants();

        if (isset($prefix)) {
            $result = [];
            $prefix = strtoupper($prefix);
            foreach ($constants as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $result[$key] = $value;
                }
            }
            return $result;
        } else {
            return $constants;
        }
    }

    /**
     * Get a class constant value based on suffix and prefix.
     * Returns null if not found.
     * @param $prefix
     * @param $suffix
     * @return mixed|null
     */
    public static function constantValue($prefix, $suffix)
    {
        $name = strtoupper($prefix . '_' . $suffix);

        if (defined("static::$name")) {
            return constant("static::$name");
        }

        return null;
    }

    /**
     * Convert to the structured data that would be put into the
     * serialised JSON form.
     */
    public function toData($assoc = false)
    {
        return json_decode(json_encode($this), $assoc);
    }
}
