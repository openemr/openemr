<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router\Console;

use Traversable;
use Zend\Mvc\Router\Exception;
use Zend\Mvc\Router\RouteInvokableFactory;
use Zend\Mvc\Router\SimpleRouteStack as BaseSimpleRouteStack;
use Zend\ServiceManager\Config;
use Zend\Stdlib\ArrayUtils;

/**
 * Tree search implementation.
 */
class SimpleRouteStack extends BaseSimpleRouteStack
{
    /**
     * init(): defined by SimpleRouteStack.
     *
     * @see    BaseSimpleRouteStack::init()
     */
    protected function init()
    {
        (new Config([
            'aliases' => [
                'catchall' => Catchall::class,
                'catchAll' => Catchall::class,
                'Catchall' => Catchall::class,
                'CatchAll' => Catchall::class,
                'simple'   => Simple::class,
                'Simple'   => Simple::class,
            ],
            'factories' => [
                Catchall::class => RouteInvokableFactory::class,
                Simple::class   => RouteInvokableFactory::class,

                // v2 normalized names
                'zendmvcrouterconsolecatchall' => RouteInvokableFactory::class,
                'zendmvcrouterconsolesimple'   => RouteInvokableFactory::class,
            ],
        ]))->configureServiceManager($this->routePluginManager);
    }

    /**
     * addRoute(): defined by RouteStackInterface interface.
     *
     * @see    RouteStackInterface::addRoute()
     * @param  string  $name
     * @param  mixed   $route
     * @param  int $priority
     * @return SimpleRouteStack
     */
    public function addRoute($name, $route, $priority = null)
    {
        if (!$route instanceof RouteInterface) {
            $route = $this->routeFromArray($route);
        }

        return parent::addRoute($name, $route, $priority);
    }

    /**
     * routeFromArray(): defined by SimpleRouteStack.
     *
     * @see    BaseSimpleRouteStack::routeFromArray()
     * @param  array|Traversable $specs
     * @return RouteInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    protected function routeFromArray($specs)
    {
        if ($specs instanceof Traversable) {
            $specs = ArrayUtils::iteratorToArray($specs);
        }

        if (! is_array($specs)) {
            throw new Exception\InvalidArgumentException('Route definition must be an array or Traversable object');
        }

        // default to 'simple' console route
        if (! isset($specs['type'])) {
            $specs['type'] = Simple::class;
        }

        // build route object
        $route = parent::routeFromArray($specs);

        if (! $route instanceof RouteInterface) {
            throw new Exception\RuntimeException('Given route does not implement Console route interface');
        }

        return $route;
    }
}
