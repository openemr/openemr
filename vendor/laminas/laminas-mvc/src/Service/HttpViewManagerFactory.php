<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\View\Http\ViewManager as HttpViewManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HttpViewManagerFactory implements FactoryInterface
{
    /**
     * Create and return a view manager for the HTTP environment
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return HttpViewManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new HttpViewManager();
    }
}
