<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\Oci8\Feature;

use Laminas\Db\Adapter\Driver\Feature\AbstractFeature;
use Laminas\Db\Adapter\Driver\Oci8\Statement;

/**
 * Class for count of results of a select
 */
class RowCounter extends AbstractFeature
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'RowCounter';
    }

    /**
     * @param Statement $statement
     * @return null|int
     */
    public function getCountForStatement(Statement $statement)
    {
        $countStmt = clone $statement;
        $sql = $statement->getSql();
        if ($sql == '' || stripos(strtolower($sql), 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result = $countStmt->execute();
        $countRow = $result->current();
        return $countRow['count'];
    }

    /**
     * @param string $sql
     * @return null|int
     */
    public function getCountForSql($sql)
    {
        if (stripos(strtolower($sql), 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $result = $this->driver->getConnection()->execute($countSql);
        $countRow = $result->current();
        return $countRow['count'];
    }

    /**
     * @param \Laminas\Db\Adapter\Driver\Oci8\Statement|string $context
     * @return callable
     */
    public function getRowCountClosure($context)
    {
        $rowCounter = $this;
        return function () use ($rowCounter, $context) {
            /** @var $rowCounter RowCounter */
            return ($context instanceof Statement)
                ? $rowCounter->getCountForStatement($context)
                : $rowCounter->getCountForSql($context);
        };
    }
}
