<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\ResultSet;

use ArrayObject;
use Laminas\Hydrator\ArraySerializable;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\Hydrator\HydratorInterface;

class HydratingResultSet extends AbstractResultSet
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator = null;

    /**
     * @var null|object
     */
    protected $objectPrototype = null;

    /**
     * Constructor
     *
     * @param  null|HydratorInterface $hydrator
     * @param  null|object $objectPrototype
     */
    public function __construct(HydratorInterface $hydrator = null, $objectPrototype = null)
    {
        $defaultHydratorClass = class_exists(ArraySerializableHydrator::class)
            ? ArraySerializableHydrator::class
            : ArraySerializable::class;
        $this->setHydrator($hydrator ?: new $defaultHydratorClass());
        $this->setObjectPrototype(($objectPrototype) ?: new ArrayObject);
    }

    /**
     * Set the row object prototype
     *
     * @param  object $objectPrototype
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setObjectPrototype($objectPrototype)
    {
        if (! is_object($objectPrototype)) {
            throw new Exception\InvalidArgumentException(
                'An object must be set as the object prototype, a ' . gettype($objectPrototype) . ' was provided.'
            );
        }
        $this->objectPrototype = $objectPrototype;
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return object
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }

    /**
     * Set the hydrator to use for each row object
     *
     * @param HydratorInterface $hydrator
     * @return self Provides a fluent interface
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get the hydrator to use for each row object
     *
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Iterator: get current item
     *
     * @return object|null
     */
    public function current()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data = $this->dataSource->current();
        $current = is_array($data) ? $this->hydrator->hydrate($data, clone $this->objectPrototype) : null;

        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $current;
        }

        return $current;
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $return = [];
        foreach ($this as $row) {
            $return[] = $this->hydrator->extract($row);
        }
        return $return;
    }
}
