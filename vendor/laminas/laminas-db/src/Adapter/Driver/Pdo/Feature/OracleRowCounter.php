<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\Pdo\Feature;

use Laminas\Db\Adapter\Driver\Feature\AbstractFeature;
use Laminas\Db\Adapter\Driver\Pdo;

/**
 * OracleRowCounter
 */
class OracleRowCounter extends AbstractFeature
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'OracleRowCounter';
    }

    /**
     * @param \Laminas\Db\Adapter\Driver\Pdo\Statement $statement
     * @return int
     */
    public function getCountForStatement(Pdo\Statement $statement)
    {
        $countStmt = clone $statement;
        $sql = $statement->getSql();
        if ($sql == '' || stripos($sql, 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result = $countStmt->execute();
        $countRow = $result->getResource()->fetch(\PDO::FETCH_ASSOC);
        unset($statement, $result);
        return $countRow['count'];
    }

    /**
     * @param $sql
     * @return null|int
     */
    public function getCountForSql($sql)
    {
        if (stripos($sql, 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as count FROM (' . $sql . ')';
        /** @var $pdo \PDO */
        $pdo = $this->driver->getConnection()->getResource();
        $result = $pdo->query($countSql);
        $countRow = $result->fetch(\PDO::FETCH_ASSOC);
        return $countRow['count'];
    }

    /**
     * @param $context
     * @return \Closure
     */
    public function getRowCountClosure($context)
    {
        return function () use ($context) {
            return ($context instanceof Pdo\Statement)
                ? $this->getCountForStatement($context)
                : $this->getCountForSql($context);
        };
    }
}
