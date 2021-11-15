<?php

namespace Academe\AuthorizeNet\Response;

/**
 *
 */

trait HasDataTrait
{
    /**
     * The raw data passed in during construction.
     */
    protected $data;

    /**
     * Get an element from a nested array, nested objects, or mix of the two.
     * The key uses "dot notation" to walk the nested data structure.
     *
     * @param array|object $target The data structure to walk.
     * @param string $key The location of the data in "dot notation"
     * @param mixed $default The value if the key is not found
     * @return mixed
     */
    public function getDataValue($key, $default = null)
    {
        $target = $this->data;

        if (is_null($key) || trim($key) == '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
                continue;
            }
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
                continue;
            }
            return static::_value($default);
        }
        return $target;
    }

    /**
     * @param $value
     */
    protected static function _value($value)
    {
        if ($value instanceof Closure) {
            return $value();
        } else {
            return $value;
        }
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
