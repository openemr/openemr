<?php

namespace OpenEMR\RestControllers\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionCleanupListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        if (!$event->getRequest()->attributes->has('is_local_api')) {
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
