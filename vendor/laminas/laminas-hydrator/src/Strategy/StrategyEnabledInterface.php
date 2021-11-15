<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Strategy;

interface StrategyEnabledInterface
{
    /**
     * Adds the given strategy under the given name.
     */
    public function addStrategy(string $name, StrategyInterface $strategy) : void;

    /**
     * Gets the strategy with the given name.
     */
    public function getStrategy(string $name) : StrategyInterface;

    /**
     * Checks if the strategy with the given name exists.
     */
    public function hasStrategy(string $name) : bool;

    /**
     * Removes the strategy with the given name.
     */
    public function removeStrategy(string $name) : void;
}
