<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Controller\Plugin;

use Laminas\Stdlib\DispatchableInterface as Dispatchable;

abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var null|Dispatchable
     */
    protected $controller;

    /**
     * Set the current controller instance
     *
     * @param  Dispatchable $controller
     * @return void
     */
    public function setController(Dispatchable $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the current controller instance
     *
     * @return null|Dispatchable
     */
    public function getController()
    {
        return $this->controller;
    }
}
