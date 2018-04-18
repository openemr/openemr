<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator;

interface NamingStrategyEnabledInterface
{
    /**
     * Adds the given naming strategy
     *
     * @param NamingStrategy\NamingStrategyInterface $strategy The naming to register.
     * @return self
     */
    public function setNamingStrategy(NamingStrategy\NamingStrategyInterface $strategy);

    /**
     * Gets the naming strategy.
     *
     * @return NamingStrategy\NamingStrategyInterface
     */
    public function getNamingStrategy();

    /**
     * Checks if a naming strategy exists.
     *
     * @return bool
     */
    public function hasNamingStrategy();

    /**
     * Removes the naming with the given name.
     *
     * @return self
     */
    public function removeNamingStrategy();
}
