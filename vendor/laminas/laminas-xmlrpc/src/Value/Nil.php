<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

class Nil extends AbstractScalar
{
    /**
     * Set the value of a nil native type
     *
     */
    public function __construct()
    {
        $this->type = self::XMLRPC_TYPE_NIL;
        $this->value = null;
    }

    /**
     * Return the value of this object, convert the XML-RPC native nill value into a PHP NULL
     *
     * @return null
     */
    public function getValue()
    {
        return;
    }
}
