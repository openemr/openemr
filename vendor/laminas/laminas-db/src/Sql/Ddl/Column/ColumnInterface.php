<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Column;

use Laminas\Db\Sql\ExpressionInterface;

/**
 * Interface ColumnInterface describes the protocol on how Column objects interact
 *
 * @package Laminas\Db\Sql\Ddl\Column
 */
interface ColumnInterface extends ExpressionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return bool
     */
    public function isNullable();

    /**
     * @return null|string|int
     */
    public function getDefault();

    /**
     * @return array
     */
    public function getOptions();
}
