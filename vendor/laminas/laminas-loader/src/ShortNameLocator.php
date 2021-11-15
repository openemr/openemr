<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Loader;

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
