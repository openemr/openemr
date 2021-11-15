<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

class Text extends AbstractScalar
{
    /**
     * Set the value of a string native type
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->type = self::XMLRPC_TYPE_STRING;

        // Make sure this value is string and all XML characters are encoded
        $this->value = (string) $value;
    }

    /**
     * Return the value of this object, convert the XML-RPC native string value into a PHP string
     *
     * @return string
     */
    public function getValue()
    {
        return (string) $this->value;
    }
}
