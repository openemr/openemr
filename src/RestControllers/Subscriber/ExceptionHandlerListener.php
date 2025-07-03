<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Core\OEHttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionHandlerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::EXCEPTION => 'onExceptionEvent',
        ];
    }

    public function onExceptionEvent(ExceptionEvent $event) : void
    {
        // this is where we handle exceptions that occur during the request
        // we can log the exception, return a custom response, etc.
        $exception = $event->getThrowable();
        if ($event->getKernel() instanceof OEHttpKernel) {
            $event->getKernel()->getSystemLogger()->error("ExceptionHandlerListener exception", ['exception' => $exception]);
        }
        $data = [
            'error' => 'An error occurred',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        $response = new Response(json_encode($data), $exception->getCode());
        $event->setResponse($response);
    }
}
