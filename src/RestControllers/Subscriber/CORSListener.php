<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CORSListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 25]],
            KernelEvents::RESPONSE => [['onKernelResponse', 0]]
        ];
    }
    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->hasResponse()) {
            // If the event already has a response, we do not need to process it further.
            // This can happen if a previous listener has already handled the request.
            return;
        }

        if (!$event->getRequest()->headers->has('Origin')) {
            return; // No CORS headers if no Origin header is present
        }
        $request = $event->getRequest();
        if ($request->getMethod() === 'OPTIONS') {
            // If the request is an OPTIONS request, we can return an initial response.
            $response = $this->getInitialResponse($request);
            $event->setResponse($response);
            return;
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if (!$request->headers->has('Origin')) {
            return; // No CORS headers if no Origin header is present
        }

        // we have to allow public API clients to have CROSS ORIGIN access
        // we could tighten things up by restricting confidential clients to not have CORS, but that limits us
        // @TODO: review security implications if we need to tighten this up
        $origins = $request->getHeader('Origin');
        $response->headers->set("Access-Control-Allow-Origin", $origins[0]);
        $event->setResponse($response);
    }

    private function getInitialResponse(HttpRestRequest $request): Response
    {
        // This method is intended to return an initial response.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to return a default response or an error response.
        $response = new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Credentials' => 'true',
            "Access-Control-Allow-Headers" => "origin, authorization, accept, content-type, content-encoding, x-requested-with",
            "Access-Control-Allow-Methods", "GET, HEAD, POST, PUT, DELETE, PATCH, TRACE, OPTIONS"
        ]);
        $origins = $request->getHeader('Origin');
        // TODO: @adunsulag should we allow all origins or just the first one?
        $response->headers->set("Access-Control-Allow-Origin", $origins[0]);
        $response->setContent('');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}
