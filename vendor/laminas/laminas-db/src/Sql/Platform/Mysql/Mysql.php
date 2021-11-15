<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Platform\Mysql;

use Laminas\Db\Sql\Platform\AbstractPlatform;

class Mysql extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator('Laminas\Db\Sql\Select', new SelectDecorator());
        $this->setTypeDecorator('Laminas\Db\Sql\Ddl\CreateTable', new Ddl\CreateTableDecorator());
        $this->setTypeDecorator('Laminas\Db\Sql\Ddl\AlterTable', new Ddl\AlterTableDecorator());
    }
}
