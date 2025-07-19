<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\FHIR\Finder\FhirRouteFinder;
use OpenEMR\RestControllers\Finder\IRouteFinder;
use OpenEMR\RestControllers\Finder\StandardRouteFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RoutesExtensionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 40]]
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
        $kernel = $event->getKernel();
        if (!$kernel instanceof OEHttpKernel) {
            // If the kernel is not an instance of OEHttpKernel, we cannot proceed with route extension.
            return;
        }
        if (!$request instanceof HttpRestRequest) {
            return; // If the request is not an instance of HttpRestRequest, we cannot proceed with route extension.
        }
        // CORS request is handled by a separate listener, so we do not need to handle it here.

        // handle each type of request separately
        if ($request->isFhirRequest()) {
            $this->processFhirRequest($request, $kernel);
        } else if ($request->isPatientRequest()) {
            $this->processPatientPortalRequest($request, $kernel);
        } else {
            $this->processStandardRequest($request, $kernel);
        }
    }

    private function processFhirRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        if ($request->isFhirSearchRequest()) {
            $request = $this->normalizeFhirSearchRequest($request);
        }
        return $this->dispatch($kernel, new FhirRouteFinder($kernel), $request, $kernel->getSystemLogger());
    }

    private function processStandardRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        return $this->dispatch($kernel, new StandardRouteFinder($kernel), $request, $kernel->getSystemLogger());
    }

    private function processPatientPortalRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        return $this->dispatch($kernel, new PatientRouteFinder($kernel), $request, $kernel->getSystemLogger());
    }

    private function dispatch(OEHttpKernel $kernel, IRouteFinder $finder, HttpRestRequest $dispatchRestRequest, SystemLogger $logger): HttpRestRequest
    {
        $routes = $finder->find($dispatchRestRequest);
        $dispatcher = new HttpRestRouteHandler($kernel);
        return $dispatcher->dispatch($routes, $dispatchRestRequest);
    }

    private function normalizeFhirSearchRequest(HttpRestRequest $dispatchRestRequest): HttpRestRequest
    {

        // in FHIR a POST request to a resource/_search is identical to the equivalent GET request with parameters.
        // POST requests are application/x-www-form-urlencoded and parameters may appear both in the URL and the request
        // body. The spec says that putting requests into both the body and query string is the same as repeating the
        // parameter.  In our case we treat the parameter as a union search if it appears in both the query string and
        // the post body.
        $normalizedRequest = clone $dispatchRestRequest;
        // chop off the back
        $pos = strripos($dispatchRestRequest->getRequestPath(), "/_search");
        if ($pos === false) {
            throw new \BadMethodCallException("Attempted to normalize search request on a path that does not contain search");
        }

        $requestPath = substr($dispatchRestRequest->getRequestPath(), 0, $pos);
        $normalizedRequest->setRequestPath($requestPath);
        $queryVars = $normalizedRequest->getQueryParams();
        $normalizedRequest->setRequestMethod("GET");

        // grab any post vars and stuff them into our query vars
        // @see https://www.hl7.org/fhir/http.html#search
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (isset($queryVars[$key])) {
                    $queryVars[$key] = is_array($queryVars[$key]) ? $queryVars[$key] : [$queryVars[$key]];
                    $queryVars[$key][] = $value;
                } else {
                    $queryVars[$key] = $value;
                }
            }
        }
        $normalizedRequest->setQueryParams($queryVars);
        return $normalizedRequest;
    }
}
