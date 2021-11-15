<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console\RouteMatcher;

interface RouteMatcherInterface
{
    /**
     * Match parameters against route passed to constructor
     *
     * @param array $params
     * @return array|null
     */
    public function match($params);
}
