<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Constraint;

class Check extends AbstractConstraint
{
    /**
     * @var string|\Laminas\Db\Sql\ExpressionInterface
     */
    protected $expression;

    /**
     * {@inheritDoc}
     */
    protected $specification = 'CHECK (%s)';

    /**
     * @param  string|\Laminas\Db\Sql\ExpressionInterface $expression
     * @param  null|string $name
     */
    public function __construct($expression, $name)
    {
        $this->expression = $expression;
        $this->name       = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpressionData()
    {
        $newSpecTypes = [self::TYPE_LITERAL];
        $values       = [$this->expression];
        $newSpec      = '';

        if ($this->name) {
            $newSpec .= $this->namedSpecification;

            array_unshift($values, $this->name);
            array_unshift($newSpecTypes, self::TYPE_IDENTIFIER);
        }

        return [[
            $newSpec . $this->specification,
            $values,
            $newSpecTypes,
        ]];
    }
}
