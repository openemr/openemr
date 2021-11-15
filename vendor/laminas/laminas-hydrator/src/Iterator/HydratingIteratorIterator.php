<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Iterator;

use Iterator;
use IteratorIterator;
use Laminas\Hydrator\Exception\InvalidArgumentException;
use Laminas\Hydrator\HydratorInterface;

use function class_exists;
use function is_object;
use function sprintf;

class HydratingIteratorIterator extends IteratorIterator implements HydratingIteratorInterface
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var object
     */
    protected $prototype;

    /**
     * @param HydratorInterface $hydrator
     * @param Iterator $data
     * @param string|object $prototype Object or class name to use for prototype.
     */
    public function __construct(HydratorInterface $hydrator, Iterator $data, $prototype)
    {
        $this->setHydrator($hydrator);
        $this->setPrototype($prototype);
        parent::__construct($data);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException if $prototype is a string, but refers to
     *     a non-existent class.
     */
    public function setPrototype($prototype) : void
    {
        if (is_object($prototype)) {
            $this->prototype = $prototype;
            return;
        }

        if (! class_exists($prototype)) {
            throw new InvalidArgumentException(
                sprintf('Method %s was passed an invalid class name: %s', __METHOD__, $prototype)
            );
        }

        $this->prototype = new $prototype;
    }

    /**
     * @inheritdoc
     */
    public function setHydrator(HydratorInterface $hydrator) : void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return object Returns hydrated clone of $prototype
     */
    public function current()
    {
        $currentValue = parent::current();
        $object       = clone $this->prototype;
        $this->hydrator->hydrate($currentValue, $object);
        return $object;
    }
}
