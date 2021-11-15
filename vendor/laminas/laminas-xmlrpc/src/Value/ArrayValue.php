<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

class ArrayValue extends AbstractCollection
{
    /**
     * Set the value of an array native type
     *
     * @param array $value
     */
    public function __construct($value)
    {
        $this->type = self::XMLRPC_TYPE_ARRAY;
        parent::__construct($value);
    }

    /**
     * Generate the XML code that represent an array native MXL-RPC value
     *
     * @return void
     */
    protected function generate()
    {
        $generator = $this->getGenerator();
        $generator
            ->openElement('value')
            ->openElement('array')
            ->openElement('data');

        if (is_array($this->value)) {
            foreach ($this->value as $val) {
                $val->generateXml();
            }
        }

        $generator
            ->closeElement('data')
            ->closeElement('array')
            ->closeElement('value');
    }
}
