<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Router;

use Laminas\Console\Request as ConsoleRequest;
use Laminas\Console\RouteMatcher\DefaultRouteMatcher;
use Laminas\Console\RouteMatcher\RouteMatcherInterface;
use Laminas\Mvc\Console\Exception;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface as Request;
use Traversable;

class Simple implements RouteInterface
{
    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = [];

    /**
     * @var RouteMatcherInterface
     */
    protected $matcher;

    /**
     * Create a new simple console route.
     *
     * @param  string|RouteMatcherInterface             $routeOrRouteMatcher
     * @param  array                                    $constraints
     * @param  array                                    $defaults
     * @param  array                                    $aliases
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        $routeOrRouteMatcher,
        array $constraints = [],
        array $defaults = [],
        array $aliases = []
    ) {
        if (is_string($routeOrRouteMatcher)) {
            $this->matcher = new DefaultRouteMatcher($routeOrRouteMatcher, $constraints, $defaults, $aliases);
        } elseif ($routeOrRouteMatcher instanceof RouteMatcherInterface) {
            $this->matcher = $routeOrRouteMatcher;
        } else {
            throw new Exception\InvalidArgumentException(
                "routeOrRouteMatcher should either be string, or class implementing RouteMatcherInterface. "
                . gettype($routeOrRouteMatcher) . " was given."
            );
        }
    }

    /**
     * factory(): defined by Route interface.
     *
     * @see    \Laminas\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return self
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

        if (! isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        foreach ([
            'constraints',
            'defaults',
            'aliases',
        ] as $opt) {
            if (! isset($options[$opt])) {
                $options[$opt] = [];
            }
        }

        return new static(
            $options['route'],
            $options['constraints'],
            $options['defaults'],
            $options['aliases']
        );
    }

    /**
     * match(): defined by Route interface.
     *
     * @see     \Laminas\Router\Route::match()
     * @param   Request $request
     * @param   null|int $pathOffset
     * @return  RouteMatch
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (! $request instanceof ConsoleRequest) {
            return;
        }

        $params  = $request->getParams()->toArray();
        $matches = $this->matcher->match($params);

        if (null !== $matches) {
            return new RouteMatch($matches);
        }
        return;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    \Laminas\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = [])
    {
        $this->assembledParams = [];
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
