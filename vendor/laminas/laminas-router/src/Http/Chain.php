<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\PriorityList;
use Laminas\Router\RoutePluginManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface as Request;
use Traversable;

/**
 * Chain route.
 */
class Chain extends TreeRouteStack implements RouteInterface
{
    /**
     * Chain routes.
     *
     * @var array
     */
    protected $chainRoutes;

    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = [];

    /**
     * Create a new part route.
     *
     * @param  array              $routes
     * @param  RoutePluginManager $routePlugins
     * @param  ArrayObject|null   $prototypes
     */
    public function __construct(array $routes, RoutePluginManager $routePlugins, ArrayObject $prototypes = null)
    {
        $this->chainRoutes         = array_reverse($routes);
        $this->routePluginManager  = $routePlugins;
        $this->routes              = new PriorityList();
        $this->prototypes          = $prototypes;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::factory()
     * @param  mixed $options
     * @throws Exception\InvalidArgumentException
     * @return Part
     */
    public static function factory($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of options',
                __METHOD__
            ));
        }

        if (! isset($options['routes'])) {
            throw new Exception\InvalidArgumentException('Missing "routes" in options array');
        }

        if (! isset($options['prototypes'])) {
            $options['prototypes'] = null;
        }

        if ($options['routes'] instanceof Traversable) {
            $options['routes'] = ArrayUtils::iteratorToArray($options['child_routes']);
        }

        if (! isset($options['route_plugins'])) {
            throw new Exception\InvalidArgumentException('Missing "route_plugins" in options array');
        }

        return new static(
            $options['routes'],
            $options['route_plugins'],
            $options['prototypes']
        );
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::match()
     * @param  Request  $request
     * @param  int|null $pathOffset
     * @param  array    $options
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null, array $options = [])
    {
        if (! method_exists($request, 'getUri')) {
            return;
        }

        if ($pathOffset === null) {
            $mustTerminate = true;
            $pathOffset    = 0;
        } else {
            $mustTerminate = false;
        }

        if ($this->chainRoutes !== null) {
            $this->addRoutes($this->chainRoutes);
            $this->chainRoutes = null;
        }

        $match      = new RouteMatch([]);
        $uri        = $request->getUri();
        $pathLength = strlen($uri->getPath());

        foreach ($this->routes as $route) {
            $subMatch = $route->match($request, $pathOffset, $options);

            if ($subMatch === null) {
                return;
            }

            $match->merge($subMatch);
            $pathOffset += $subMatch->getLength();
        }

        if ($mustTerminate && $pathOffset !== $pathLength) {
            return;
        }

        return $match;
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = [])
    {
        if ($this->chainRoutes !== null) {
            $this->addRoutes($this->chainRoutes);
            $this->chainRoutes = null;
        }

        $this->assembledParams = [];

        $routes = ArrayUtils::iteratorToArray($this->routes);

        end($routes);
        $lastRouteKey = key($routes);
        $path         = '';

        foreach ($routes as $key => $route) {
            $chainOptions = $options;
            $hasChild     = isset($options['has_child']) ? $options['has_child'] : false;

            $chainOptions['has_child'] = ($hasChild || $key !== $lastRouteKey);

            $path   .= $route->assemble($params, $chainOptions);
            $params  = array_diff_key($params, array_flip($route->getAssembledParams()));

            $this->assembledParams += $route->getAssembledParams();
        }

        return $path;
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
