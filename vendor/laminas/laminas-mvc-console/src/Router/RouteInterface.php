<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Router;

use Laminas\Router\RouteInterface as BaseRoute;

interface RouteInterface extends BaseRoute
{
    /**
     * Get a list of parameters used while assembling.
     *
     * @return array
     */
    public function getAssembledParams();
}
