<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Predicate;

use Laminas\Db\Sql\AbstractExpression;

class IsNull extends AbstractExpression implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = '%1$s IS NULL';

    /**
     * @var
     */
    protected $identifier;

    /**
     * Constructor
     *
     * @param  string $identifier
     */
    public function __construct($identifier = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param  string $identifier
     * @return self Provides a fluent interface
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set specification string to use in forming SQL predicate
     *
     * @param  string $specification
     * @return self Provides a fluent interface
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * Get specification string to use in forming SQL predicate
     *
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * Get parts for where statement
     *
     * @return array
     */
    public function getExpressionData()
    {
        $identifier = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        return [[
            $this->getSpecification(),
            [$identifier[0]],
            [$identifier[1]],
        ]];
    }
}
