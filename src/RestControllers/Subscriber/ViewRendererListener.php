<?php

namespace OpenEMR\RestControllers\Subscriber;

use Nyholm\Psr7\Response as Psr7Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewRendererListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['onViewEvent', 50]]
        ];
    }

    public function onViewEvent(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if ($result instanceof Psr7Response) {
            // If the controller result is a PSR-7 Response, we need to convert it to a Symfony Response.
            // This is useful for cases where the controller returns a PSR-7 response directly when it wants
            // to set headers,etc.
            $httpFoundationFactory = new HttpFoundationFactory();
            $event->setResponse($httpFoundationFactory->createResponse($result));
            return;
        }
        if ($result instanceof Response) {
            // If the controller result is already a Symfony Response, we can just set it directly.
            $event->setResponse($result);
            return;
        }

        // Preserve the status previously selected by http_response_code()
        // — RestControllerHelper::handleProcessingResult() sets it (e.g.
        // 404 for an empty single-GET lookup, 400 for validation errors)
        // but the JsonResponse / Response constructors default to 200
        // and would silently drop it. 12+ `handleProcessingResult(…,
        // 4xx)` call sites across controllers depend on this.
        //
        // http_response_code() returns int when a code has been set and
        // `false` (NOT bool `true` — but PHPStan models it as int|true
        // through its stubs) when called outside an HTTP context. Guard
        // with an is_int narrow + fall back to 200 for any non-int.
        $capturedCode = http_response_code();
        $statusCode = is_int($capturedCode) ? $capturedCode : Response::HTTP_OK;
        // TODO: @adunsulag we could add in logic for things like ndjson, xml, etc here.
        match ($event->getRequest()->getContentTypeFormat()) {
            'text' => $event->setResponse(new Response((string)$result, $statusCode, [
                'Content-Type' => 'text/plain; charset=UTF-8'
            ])),
            // Handle JSON response
            default => $event->setResponse(new JsonResponse($result, $statusCode)),
        };
    }
}
