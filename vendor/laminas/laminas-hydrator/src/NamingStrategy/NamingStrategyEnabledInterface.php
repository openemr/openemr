<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\NamingStrategy;

interface NamingStrategyEnabledInterface
{
    /**
     * Adds the given naming strategy
     */
    public function setNamingStrategy(NamingStrategyInterface $strategy) : void;

    /**
     * Gets the naming strategy.
     */
    public function getNamingStrategy() : NamingStrategyInterface;

    /**
     * Checks if a naming strategy exists.
     */
    public function hasNamingStrategy() : bool;

    /**
     * Removes the naming with the given name.
     */
    public function removeNamingStrategy() : void;
}
