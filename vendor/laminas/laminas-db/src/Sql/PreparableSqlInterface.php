<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\StatementContainerInterface;

interface PreparableSqlInterface
{
    /**
     * @param AdapterInterface            $adapter
     * @param StatementContainerInterface $statementContainer
     *
     * @return void
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer);
}
