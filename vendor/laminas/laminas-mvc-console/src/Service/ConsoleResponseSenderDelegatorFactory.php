<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\ResponseSender\ConsoleResponseSender;
use Laminas\Mvc\ResponseSender\SendResponseEvent;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Delegator factory for the SendResponseListener.
 *
 * Injects the ConsoleResponseSender as an event listener on the SendResponseListener
 * prior to returning it.
 */
class ConsoleResponseSenderDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Laminas\Mvc\SendResponseListener
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $sendResponseListener = $callback();
        $events = $sendResponseListener->getEventManager();
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new ConsoleResponseSender(), -2000);
        return $sendResponseListener;
    }

    /**
     * laminas-servicemanager v2 compatibility.
     *
     * Proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Laminas\Mvc\SendResponseListener
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
