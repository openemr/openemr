<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Platform\Sqlite;

use Laminas\Db\Sql\Platform\AbstractPlatform;

class Sqlite extends AbstractPlatform
{
    /**
     * Constructor
     *
     * Registers the type decorator.
     */
    public function __construct()
    {
        $this->setTypeDecorator('Laminas\Db\Sql\Select', new SelectDecorator());
    }
}
