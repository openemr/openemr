<?php

namespace OpenEMR\RestControllers\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewRendererListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::VIEW => [['onViewEvent', 50]]
        ];
    }

    public function onViewEvent(ViewEvent $event) : void
    {
        $result = $event->getControllerResult();

        // TODO: @adunsulag we could add in logic for things like ndjson, xml, etc here.
        switch ($event->getRequest()->getContentTypeFormat()) {
            case 'text':
                $event->setResponse(new Response((string)$result, 200, [
                    'Content-Type' => 'text/plain; charset=UTF-8'
                ]));
                break;
            case 'json':
            default:
                // Handle JSON response
                $event->setResponse(new JsonResponse($result));
                break;
        }
    }
}
