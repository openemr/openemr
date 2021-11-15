<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Column;

class Boolean extends Column
{
    /**
     * @var string
     */
    protected $type = 'BOOLEAN';

    /**
     * {@inheritDoc}
     */
    protected $isNullable = false;

    /**
     * {@inheritDoc}
     */
    public function setNullable($nullable)
    {
        return parent::setNullable(false);
    }
}
