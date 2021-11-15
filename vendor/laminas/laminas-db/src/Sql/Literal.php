<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

class Literal implements ExpressionInterface
{
    /**
     * @var string
     */
    protected $literal = '';

    /**
     * @param $literal
     */
    public function __construct($literal = '')
    {
        $this->literal = $literal;
    }

    /**
     * @param string $literal
     * @return self Provides a fluent interface
     */
    public function setLiteral($literal)
    {
        $this->literal = $literal;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiteral()
    {
        return $this->literal;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        return [[
            str_replace('%', '%%', $this->literal),
            [],
            []
        ]];
    }
}
