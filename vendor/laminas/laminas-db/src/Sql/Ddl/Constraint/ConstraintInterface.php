<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Constraint;

use Laminas\Db\Sql\ExpressionInterface;

interface ConstraintInterface extends ExpressionInterface
{
    public function getColumns();
}
