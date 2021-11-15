<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Platform;

class Sql92 extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'SQL92';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        trigger_error(
            'Attempting to quote a value without specific driver level support'
            . ' can introduce security vulnerabilities in a production environment.'
        );
        return '\'' . addcslashes($value, "\x00\n\r\\'\"\x1a") . '\'';
    }
}
