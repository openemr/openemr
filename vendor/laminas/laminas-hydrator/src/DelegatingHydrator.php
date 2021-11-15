<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Psr\Container\ContainerInterface;

use function get_class;

class DelegatingHydrator implements HydratorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $hydrators;

    public function __construct(ContainerInterface $hydrators)
    {
        $this->hydrators = $hydrators;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data, object $object)
    {
        return $this->getHydrator($object)->hydrate($data, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(object $object) : array
    {
        return $this->getHydrator($object)->extract($object);
    }

    /**
     * Gets hydrator for an object
     */
    protected function getHydrator(object $object) : HydratorInterface
    {
        return $this->hydrators->get(get_class($object));
    }
}
