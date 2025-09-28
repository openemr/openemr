<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\FHIR\Finder\FhirRouteFinder;
use OpenEMR\RestControllers\Finder\PortalRouteFinder;
use OpenEMR\RestControllers\Finder\StandardRouteFinder;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Services\FHIR\Utils\SearchRequestNormalizer;
use OpenEMR\Services\FHIR\UtilsService;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class RoutesExtensionListener implements EventSubscriberInterface
{
    use SystemLoggerAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 40]]
        ];
    }
    public function onKernelRequest(RequestEvent $event): void
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
                (new SearchRequestNormalizer($this->getSystemLogger()))->normalizeSearchRequest($request);
            }
            $response = $this->processFhirRequest($request, $kernel);
        } else if ($request->isPatientRequest()) {
            $response = $this->processPatientPortalRequest($request, $kernel);
        } else {
            $response = $this->processStandardRequest($request, $kernel);
        }
        if ($response !== null) {
            // If a response is returned, we set it in the event
            $httpFoundation = new HttpFoundationFactory();
            $event->setResponse($httpFoundation->createResponse($response));
        }
    }

    private function processFhirRequest(HttpRestRequest $request, OEHttpKernel $kernel): ?ResponseInterface
    {
        try {
            $finder = new FhirRouteFinder($kernel);
            $routes = $finder->find($request);
            $serviceLocator = new FhirServiceLocator($routes);
            // TODO: @adunsulag I don't like this, we really need a true service container for dependency injection...
            // is there a way we can move this up higher into the ArgumentResolver? up the request processing stack?
            $request->attributes->set('_serviceLocator', $serviceLocator);
            return $this->dispatch($kernel, $routes, $request);
        } catch (Throwable $e) {
            // TODO: @adunsulag should we return an OperationOutcome here instead of throwing the exception?
            // or should OperationOutcome be handled in the ExceptionHandlerListener class?  However, that class would need to be aware of FHIR...
            // stubbing this out for now.
//            $opOutcome = UtilsService::createOperationOutcomeResource()
//            return new Response();
            throw $e;
        }
    }

    private function processStandardRequest(HttpRestRequest $request, OEHttpKernel $kernel): ?ResponseInterface
    {
        $finder = new StandardRouteFinder($kernel);
        $routes = $finder->find($request);
        return $this->dispatch($kernel, $routes, $request);
    }

    private function processPatientPortalRequest(HttpRestRequest $request, OEHttpKernel $kernel): ?ResponseInterface
    {
        $finder = new PortalRouteFinder($kernel);
        $patientRoutes = $finder->find($request);
        return $this->dispatch($kernel, $patientRoutes, $request);
    }

    private function dispatch(OEHttpKernel $kernel, array $routes, HttpRestRequest $dispatchRestRequest): ?ResponseInterface
    {
        $dispatcher = new HttpRestRouteHandler($kernel);
        return $dispatcher->dispatch($routes, $dispatchRestRequest);
    }
}
