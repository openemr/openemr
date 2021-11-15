<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Feature;

/**
 * Autoloader provider interface
 */
interface AutoloaderProviderInterface
{
    /**
     * Return an array for passing to Laminas\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig();
}
