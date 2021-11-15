<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Feature;

use Laminas\EventManager\EventInterface;

/**
 * Bootstrap listener provider interface
 */
interface BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return void
     */
    public function onBootstrap(EventInterface $e);
}
