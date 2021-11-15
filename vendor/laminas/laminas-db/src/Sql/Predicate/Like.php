<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Predicate;

use Laminas\Db\Sql\AbstractExpression;

class Like extends AbstractExpression implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = '%1$s LIKE %2$s';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var string
     */
    protected $like = '';

    /**
     * @param string $identifier
     * @param string $like
     */
    public function __construct($identifier = null, $like = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($like) {
            $this->setLike($like);
        }
    }

    /**
     * @param  string $identifier
     * @return self Provides a fluent interface
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param  string $like
     * @return self Provides a fluent interface
     */
    public function setLike($like)
    {
        $this->like = $like;
        return $this;
    }

    /**
     * @return string
     */
    public function getLike()
    {
        return $this->like;
    }

    /**
     * @param  string $specification
     * @return self Provides a fluent interface
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        list($values[], $types[]) = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        list($values[], $types[]) = $this->normalizeArgument($this->like, self::TYPE_VALUE);
        return [
            [
                $this->specification,
                $values,
                $types,
            ]
        ];
    }
}
