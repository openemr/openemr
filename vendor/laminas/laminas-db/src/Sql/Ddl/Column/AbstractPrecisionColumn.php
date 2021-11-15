<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Column;

abstract class AbstractPrecisionColumn extends AbstractLengthColumn
{
    /**
     * @var int|null
     */
    protected $decimal;

    /**
     * {@inheritDoc}
     *
     * @param int|null $decimal
     * @param int      $digits
     */
    public function __construct(
        $name,
        $digits = null,
        $decimal = null,
        $nullable = false,
        $default = null,
        array $options = []
    ) {
        $this->setDecimal($decimal);

        parent::__construct($name, $digits, $nullable, $default, $options);
    }

    /**
     * @param  int $digits
     *
     * @return self
     */
    public function setDigits($digits)
    {
        return $this->setLength($digits);
    }

    /**
     * @return int
     */
    public function getDigits()
    {
        return $this->getLength();
    }

    /**
     * @param int|null $decimal
     * @return self Provides a fluent interface
     */
    public function setDecimal($decimal)
    {
        $this->decimal = null === $decimal ? null : (int) $decimal;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLengthExpression()
    {
        if ($this->decimal !== null) {
            return $this->length . ',' . $this->decimal;
        }

        return $this->length;
    }
}
