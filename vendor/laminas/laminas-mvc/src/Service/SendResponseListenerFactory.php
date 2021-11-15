<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\SendResponseListener;

class SendResponseListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return SendResponseListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $listener = new SendResponseListener();
        $listener->setEventManager($container->get('EventManager'));
        return $listener;
    }
}
