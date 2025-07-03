<?php

namespace OpenEMR\RestControllers\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestMapperListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 50]]
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
    }

}
