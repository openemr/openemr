<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc;

use Laminas\EventManager\EventsCapableInterface;

interface ApplicationInterface extends EventsCapableInterface
{
    /**
     * Get the locator object
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager();

    /**
     * Get the request object
     *
     * @return \Laminas\Stdlib\RequestInterface
     */
    public function getRequest();

    /**
     * Get the response object
     *
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function getResponse();

    /**
     * Run the application
     *
     * @return self
     */
    public function run();
}
