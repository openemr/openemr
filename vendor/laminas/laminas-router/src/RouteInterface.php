<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Stdlib\RequestInterface as Request;

/**
 * RouteInterface interface.
 */
interface RouteInterface
{
    /**
     * Priority used for route stacks.
     *
     * @var int
     * public $priority;
     */

    /**
     * Create a new route with given options.
     *
     * @param  array|\Traversable $options
     * @return RouteInterface
     */
    public static function factory($options = []);

    /**
     * Match a given request.
     *
     * @param  Request $request
     * @return RouteMatch|null
     */
    public function match(Request $request);

    /**
     * Assemble the route.
     *
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = []);
}
