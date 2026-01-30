<?php

namespace OpenEMR\RestControllers\Subscriber;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionHandlerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onExceptionEvent',
        ];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        // this is where we handle exceptions that occur during the request
        // we can log the exception, return a custom response, etc.
        $exception = $event->getThrowable();
        if ($event->getKernel() instanceof OEHttpKernel) {
            $data = [
                'trace' => $exception->getTraceAsString()
            ];
            if ($exception->getPrevious() !== null) {
                $data['previous'] = [
                    'message' => $exception->getPrevious()->getMessage(),
                    'code' => $exception->getPrevious()->getCode(),
                    'trace' => $exception->getPrevious()->getTraceAsString()
                ];
            }
            $event->getKernel()->getSystemLogger()->error(
                "ExceptionHandlerListener exception " . $exception->getMessage(),
                $data
            );
        }
        $code = 500; // Default to 500 Internal Server Error
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        }
        if ($exception instanceof OAuthServerException) {
            $code = $exception->getHttpStatusCode();
        }
        $data = [
            'error' => 'An error occurred',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        $response = new JsonResponse($data, $code);
        $event->setResponse($response);
    }
}
