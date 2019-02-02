<?php
/**
 * @see       https://github.com/zendframework/zend-loader for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-loader/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Loader;

/**
 * Short name locator interface
 */
interface ShortNameLocator
{
    /**
     * Whether or not a Helper by a specific name
     *
     * @param  string $name
     * @return bool
     */
    public function isLoaded($name);

    /**
     * Return full class name for a named helper
     *
     * @param  string $name
     * @return string
     */
    public function getClassName($name);

    /**
     * Load a helper via the name provided
     *
     * @param  string $name
     * @return string
     */
    public function load($name);
}
