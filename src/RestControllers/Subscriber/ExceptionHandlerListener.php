<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            $event->getKernel()->getSystemLogger()->error("ExceptionHandlerListener exception " . $exception->getMessage()
                , ['trace' => $exception->getTraceAsString()]);
        }
        $code = 500; // Default to 500 Internal Server Error
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        }
        $data = [
            'error' => 'An error occurred',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        $response = new Response(json_encode($data), $code);
        $event->setResponse($response);
    }
}
