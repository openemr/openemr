<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

class Base64 extends AbstractScalar
{
    /**
     * Set the value of a base64 native type
     * We keep this value in base64 encoding
     *
     * @param string $value
     * @param bool $alreadyEncoded If set, it means that the given string is already base64 encoded
     */
    public function __construct($value, $alreadyEncoded = false)
    {
        $this->type = self::XMLRPC_TYPE_BASE64;

        $value = (string) $value;    // Make sure this value is string
        if (! $alreadyEncoded) {
            $value = base64_encode($value);     // We encode it in base64
        }
        $this->value = $value;
    }

    /**
     * Return the value of this object, convert the XML-RPC native base64 value into a PHP string
     * We return this value decoded (a normal string)
     *
     * @return string
     */
    public function getValue()
    {
        return base64_decode($this->value);
    }
}
