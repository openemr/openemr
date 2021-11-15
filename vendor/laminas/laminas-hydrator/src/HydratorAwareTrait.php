<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

trait HydratorAwareTrait
{
    /**
     * Hydrator instance
     *
     * @var null|HydratorInterface
     */
    protected $hydrator;

    /**
     * Set hydrator
     */
    public function setHydrator(HydratorInterface $hydrator) : void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Retrieve hydrator
     */
    public function getHydrator() : ?HydratorInterface
    {
        return $this->hydrator;
    }
}
