<?php

namespace OpenEMR\RestControllers\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionCleanupListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $inOAuth2Flow = $session->has('oauth2_in_progress') && $session->get('oauth2_in_progress') === true;

        // we have TWO situations where we persist the session:
        // 1. if this is a local API request, we want to persist the session
        // 2. if this is a oauth2 login & patient selection, we want to persist the session
        if (!($inOAuth2Flow || $event->getRequest()->attributes->has('is_local_api'))) {
            $session->invalidate(0); // Invalidate the session without persisting it
            return;
        }

        // if we still have an active session, we close it, just to mimic the behavior of the original dispatch.php

        if ($session->isStarted()) {
            if (!empty($session->all())) {
                $session->save();
            }
        }
    }
}
