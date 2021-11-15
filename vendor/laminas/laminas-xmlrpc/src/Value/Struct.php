<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Value;

class Struct extends AbstractCollection
{
    /**
     * Set the value of a struct native type
     *
     * @param array $value
     */
    public function __construct($value)
    {
        $this->type = self::XMLRPC_TYPE_STRUCT;
        parent::__construct($value);
    }

    /**
     * Generate the XML code that represent struct native MXL-RPC value
     *
     * @return void
     */
    protected function generate()
    {
        $generator = $this->getGenerator();
        $generator
            ->openElement('value')
            ->openElement('struct');

        if (is_array($this->value)) {
            foreach ($this->value as $name => $val) {
                $generator
                    ->openElement('member')
                    ->openElement('name', $name)
                    ->closeElement('name');
                $val->generateXml();
                $generator->closeElement('member');
            }
        }

        $generator
            ->closeElement('struct')
            ->closeElement('value');
    }
}
