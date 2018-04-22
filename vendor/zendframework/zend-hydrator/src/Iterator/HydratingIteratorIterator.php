<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator\Iterator;

use Iterator;
use IteratorIterator;
use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Hydrator\HydratorInterface;

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
     */
    public function setPrototype($prototype)
    {
        if (is_object($prototype)) {
            $this->prototype = $prototype;
            return;
        }

        if (!class_exists($prototype)) {
            throw new InvalidArgumentException(
                sprintf('Method %s was passed an invalid class name: %s', __METHOD__, $prototype)
            );
        }

        $this->prototype = new $prototype;
    }

    /**
     * @inheritdoc
     */
    public function setHydrator(HydratorInterface $hydrator)
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
