<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

use Laminas\XmlRpc\AbstractValue;

abstract class AbstractScalar extends AbstractValue
{
    /**
     * Generate the XML code that represent a scalar native MXL-RPC value
     *
     * @return void
     */
    protected function generate()
    {
        $generator = $this->getGenerator();

        $generator
            ->openElement('value')
            ->openElement($this->type, $this->value)
            ->closeElement($this->type)
            ->closeElement('value');
    }
}
