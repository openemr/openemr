<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\Router\RouteMatch as LegacyRouteMatch;
use Laminas\Mvc\Router\RouteStackInterface as LegacyRouteStackInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;
use Laminas\View\Exception;
use Traversable;

/**
 * Helper for making easy links and getting urls that depend on the routes and router.
 */
class Url extends AbstractHelper
{
    /**
     * Router instance.
     *
     * @var LegacyRouteStackInterface|RouteStackInterface
     */
    protected $router;

    /**
     * Route matches returned by the router.
     *
     * @var LegacyRouteMatch|RouteMatch.
     */
    protected $routeMatch;

    /**
     * Generates a url given the name of a route.
     *
     * @see Laminas\Mvc\Router\RouteInterface::assemble()
     * @see Laminas\Router\RouteInterface::assemble()
     * @param  string $name Name of the route
     * @param  array $params Parameters for the link
     * @param  array|Traversable $options Options for the route
     * @param  bool $reuseMatchedParams Whether to reuse matched parameters
     * @return string Url For the link href attribute
     * @throws Exception\RuntimeException If no RouteStackInterface was
     *     provided
     * @throws Exception\RuntimeException If no RouteMatch was provided
     * @throws Exception\RuntimeException If RouteMatch didn't contain a
     *     matched route name
     * @throws Exception\InvalidArgumentException If the params object was not
     *     an array or Traversable object.
     */
    public function __invoke($name = null, $params = [], $options = [], $reuseMatchedParams = false)
    {
        if (null === $this->router) {
            throw new Exception\RuntimeException('No RouteStackInterface instance provided');
        }

        if (3 == func_num_args() && is_bool($options)) {
            $reuseMatchedParams = $options;
            $options = [];
        }

        if ($name === null) {
            if ($this->routeMatch === null) {
                throw new Exception\RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch->getMatchedRouteName();

            if ($name === null) {
                throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        if (! is_array($params)) {
            if (! $params instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Params is expected to be an array or a Traversable object'
                );
            }
            $params = iterator_to_array($params);
        }

        if ($reuseMatchedParams && $this->routeMatch !== null) {
            $routeMatchParams = $this->routeMatch->getParams();

            if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($routeMatchParams, $params);
        }

        $options['name'] = $name;

        return $this->router->assemble($params, $options);
    }

    /**
     * Set the router to use for assembling.
     *
     * @param LegacyRouteStackInterface|RouteStackInterface $router
     * @return Url
     * @throws Exception\InvalidArgumentException for invalid router types.
     */
    public function setRouter($router)
    {
        if (! $router instanceof RouteStackInterface
            && ! $router instanceof LegacyRouteStackInterface
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a %s or %s instance; received %s',
                __METHOD__,
                RouteStackInterface::class,
                LegacyRouteStackInterface::class,
                (is_object($router) ? get_class($router) : gettype($router))
            ));
        }

        $this->router = $router;
        return $this;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  LegacyRouteMatch|RouteMatch $routeMatch
     * @return Url
     */
    public function setRouteMatch($routeMatch)
    {
        if (! $routeMatch instanceof RouteMatch
            && ! $routeMatch instanceof LegacyRouteMatch
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a %s or %s instance; received %s',
                __METHOD__,
                RouteMatch::class,
                LegacyRouteMatch::class,
                (is_object($routeMatch) ? get_class($routeMatch) : gettype($routeMatch))
            ));
        }

        $this->routeMatch = $routeMatch;
        return $this;
    }
}
