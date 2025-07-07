<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Session\SessionUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionCleanupListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event): void
    {
        if (!$event->getRequest()->attributes->has('is_local_api')) {
            SessionUtil::apiSessionCookieDestroy();
            return;
        }

        // if we still have an active session, we close it
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
}
