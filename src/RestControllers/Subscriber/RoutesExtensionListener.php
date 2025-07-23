<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\FHIR\Finder\FhirRouteFinder;
use OpenEMR\RestControllers\Finder\IRouteFinder;
use OpenEMR\RestControllers\Finder\StandardRouteFinder;
use OpenEMR\Services\FHIR\Utils\SearchRequestNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RoutesExtensionListener implements EventSubscriberInterface
{
    use SystemLoggerAwareTrait;

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
            if ($request->isFhirSearchRequest()) {
                $updatedRequest = (new SearchRequestNormalizer($this->getSystemLogger()))->normalizeSearchRequest($request);
            }
            $this->processFhirRequest($request, $kernel);
        } else if ($request->isPatientRequest()) {
            $this->processPatientPortalRequest($request, $kernel);
        } else {
            $this->processStandardRequest($request, $kernel);
        }
    }

    private function processFhirRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
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
}
