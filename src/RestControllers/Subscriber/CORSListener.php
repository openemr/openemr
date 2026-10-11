<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Auth\OpenIDConnect\CORSAllowedOriginRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CORSListener implements EventSubscriberInterface
{
    private CORSAllowedOriginRegistry $registry;

    public function setAllowedOriginRegistry(CORSAllowedOriginRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function getAllowedOriginRegistry(): CORSAllowedOriginRegistry
    {
        $this->registry ??= new CORSAllowedOriginRegistry();
        return $this->registry;
    }

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

        $request = $event->getRequest();
        if (!$request->headers->has('Origin')) {
            return; // No CORS headers if no Origin header is present
        }
        if ($request->getMethod() === 'OPTIONS') {
            // Short-circuit the preflight here. Downstream listeners never
            // see the OPTIONS request, so headers added by onKernelResponse
            // do not reach it; set the preflight headers here instead.
            $response = $this->buildPreflightResponse($request);
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

        // Vary: Origin regardless of allow-list outcome, so a shared cache
        // never serves a response built for one origin to a request from
        // a different one.
        $response->headers->set('Vary', 'Origin', false);

        // Response-side CORS gate is bound to the authenticated OAuth2
        // client. BearerTokenAuthorizationStrategy sets the clientId
        // request attribute to a string after a token validates;
        // LocalApiAuthorizationController and SkipAuthorizationStrategy
        // both set it to null. Emit Access-Control-Allow-Origin only
        // when:
        //  - the response is for a bearer-authenticated request
        //    (clientId is a non-empty string), AND
        //  - the request Origin is one of that specific client's own
        //    registered redirect_uri origins.
        //
        // Session-cookie-authenticated responses and unauth responses
        // never carry Allow-Origin -- cross-origin JS cannot read
        // either, so a cookie-authenticated session cannot be used as a
        // reflection oracle via a self-registered attacker client's
        // origin matching the global union.
        $clientId = $request->attributes->get('clientId');
        if (!is_string($clientId) || $clientId === '') {
            $event->setResponse($response);
            return;
        }

        $origin = (string) $request->headers->get('Origin', '');
        if (!$this->getAllowedOriginRegistry()->isAllowedForClient($clientId, $origin)) {
            $event->setResponse($response);
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $event->setResponse($response);
    }

    private function buildPreflightResponse(Request $request): Response
    {
        // Preflight stays scoped to the global allow-list rather than
        // the per-client gate: CORS forbids credentials on preflight,
        // so no Authorization header is attached and the authenticated
        // OAuth2 client is not yet known. A lenient preflight that
        // accepts any origin in the global union is harmless on its
        // own; the real gate is the per-client check on the actual
        // response in onKernelResponse().
        $origin = (string) $request->headers->get('Origin', '');
        $allowed = $this->getAllowedOriginRegistry()->isAllowed($origin);

        $headers = [
            'Vary' => 'Origin',
            'Access-Control-Allow-Headers' => 'origin, authorization, accept, content-type, content-encoding, x-requested-with',
            'Access-Control-Allow-Methods' => 'GET, HEAD, POST, PUT, DELETE, PATCH, TRACE, OPTIONS',
        ];
        if ($allowed) {
            $headers['Access-Control-Allow-Origin'] = $origin;
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }
        return new Response('', Response::HTTP_OK, $headers);
    }
}
