<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Predicate;

use Laminas\Db\Sql\Expression as BaseExpression;

class Expression extends BaseExpression implements PredicateInterface
{
    /**
     * Constructor
     *
     * @param string $expression
     * @param int|float|bool|string|array $valueParameter
     */
    public function __construct($expression = null, $valueParameter = null /*[, $valueParameter, ... ]*/)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        $this->setParameters(is_array($valueParameter) ? $valueParameter : array_slice(func_get_args(), 1));
    }
}
