<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Column;

abstract class AbstractLengthColumn extends Column
{
    /**
     * @var int
     */
    protected $length;

    /**
     * {@inheritDoc}
     *
     * @param int $length
     */
    public function __construct($name, $length = null, $nullable = false, $default = null, array $options = [])
    {
        $this->setLength($length);

        parent::__construct($name, $nullable, $default, $options);
    }

    /**
     * @param  int $length
     * @return self Provides a fluent interface
     */
    public function setLength($length)
    {
        $this->length = (int) $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    protected function getLengthExpression()
    {
        return (string) $this->length;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $data = parent::getExpressionData();

        if ($this->getLengthExpression()) {
            $data[0][1][1] .= '(' . $this->getLengthExpression() . ')';
        }

        return $data;
    }
}
