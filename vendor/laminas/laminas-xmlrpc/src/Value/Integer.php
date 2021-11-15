<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

use Laminas\XmlRpc\Exception;

class Integer extends AbstractScalar
{
    /**
     * Set the value of an integer native type
     *
     * @param int $value
     * @throws Exception\ValueException
     */
    public function __construct($value)
    {
        if ($value > PHP_INT_MAX) {
            throw new Exception\ValueException('Overlong integer given');
        }

        $this->type = self::XMLRPC_TYPE_INTEGER;
        $this->value = (int) $value;    // Make sure this value is integer
    }

    /**
     * Return the value of this object, convert the XML-RPC native integer value into a PHP integer
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
}
