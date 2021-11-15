<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

use Laminas\XmlRpc\AbstractValue;

abstract class AbstractCollection extends AbstractValue
{
    /**
     * Set the value of a collection type (array and struct) native types
     *
     * @param array $value
     */
    public function __construct($value)
    {
        $values = (array) $value;   // Make sure that the value is an array
        foreach ($values as $key => $value) {
            // If the elements of the given array are not Laminas\XmlRpc\Value objects,
            // we need to convert them as such (using auto-detection from PHP value)
            if (! $value instanceof parent) {
                $value = static::getXmlRpcValue($value, self::AUTO_DETECT_TYPE);
            }
            $this->value[$key] = $value;
        }
    }

    /**
     * Return the value of this object, convert the XML-RPC native collection values into a PHP array
     *
     * @return array
     */
    public function getValue()
    {
        $values = (array) $this->value;
        foreach ($values as $key => $value) {
            $values[$key] = $value->getValue();
        }
        return $values;
    }
}
