<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\RestControllers\Authorization\IAuthorizationStrategy;
use OpenEMR\RestControllers\Authorization\LocalApiAuthorizationController;
use OpenEMR\RestControllers\Authorization\SkipAuthorizationStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationListener implements EventSubscriberInterface
{
    private SystemLogger $logger;
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 50]],
            RestApiSecurityCheckEvent::EVENT_HANDLE => [['onRestApiSecurityCheck', 50]]
        ];
    }
    private array $authorizationStrategies;
    public function __construct()
    {
    }

    public function setLogger(SystemLogger $logger): void
    {
        // This method is intended to set the logger for the authorization listener.
        // Implementation details would depend on the specific requirements of the application.
        $this->logger = $logger;
    }
    public function getLogger(): SystemLogger
    {
        // This method is intended to return the logger for the authorization listener.
        // Implementation details would depend on the specific requirements of the application.
        if (!isset($this->logger)) {
            // If the logger is not set, we can initialize it here.
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }

    public function getAuthorizationStrategies(): array
    {
        if (!isset($this->authorizationStrategies)) {
            // Initialize the authorization strategies if not already set.
            $this->authorizationStrategies = [];
            // the order of these strategies is important, as they will be checked in the order they are added.
            $this->addAuthorizationStrategy(new LocalApiAuthorizationController($this->getLogger()));
            $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
            $skipAuthorizationStrategy->addSkipRoute('/fhir/metadata');
            $skipAuthorizationStrategy->addSkipRoute('/fhir/.well-known/smart-configuration');
            $skipAuthorizationStrategy->addSkipRoute('/fhir/OperationDefinition');
            $this->addAuthorizationStrategy($skipAuthorizationStrategy);
            // TODO: @adunsulag not sure I like instantiating the ServerConfig here, perhaps we need to do this in a different way?
            $serverConfig = new ServerConfig();
            $bearerTokenAuthorizationStrategy = new BearerTokenAuthorizationStrategy($this->getLogger());
            $bearerTokenAuthorizationStrategy->setPublicKey($serverConfig->getPublicRestKey());
            $this->addAuthorizationStrategy($bearerTokenAuthorizationStrategy);
        }
        // This method is intended to return the list of authorization strategies.
        // Implementation details would depend on the specific requirements of the application.
        return $this->authorizationStrategies;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->setLogger($event->getKernel()->getSystemLogger());
        $request = $event->getRequest();
        if ($this->shouldProcessRequest($request)) {
            // If the request should be processed, authorize it.
            $this->authorizeRequest($request);
        }
    }

    /**
     * @param RestApiSecurityCheckEvent $event
     * @return RestApiSecurityCheckEvent
     * @throws AccessDeniedException
     */
    public function onRestApiSecurityCheck(RestApiSecurityCheckEvent $event): RestApiSecurityCheckEvent
    {
        $request = $event->getRestRequest();
        if ($event->shouldSkipSecurityCheck()) {
            // If the event indicates that security checks should be skipped, we don't do any further processing.
            return $event;
        }
        $restRequest = $event->getRestRequest();
        $scopeType = $event->getScopeType();
        if ($restRequest->isPatientRequest()) {
            if (empty($restRequest->getPatientUUIDString())) { // we MUST have a patient uuid string if its a patient request
                // need to fail here since this means the downstream patient binding mechanism will be broken
                throw new AccessDeniedException("patient", "demo", "Patient UUID is required for patient requests.");
            }
            // if we are a patient only request and we have a patient uuid populated (from session) then we set our scope type to be patient.
            $scopeType = 'patient';
        }

        if ($restRequest->isFhir()) {
            // don't do any checks on our open fhir resources
            if ($this->fhirRestRequestSkipSecurityCheck($restRequest)) {
                return $event;
            }
            // we do NOT want logged in patients writing data at this point so we fail
            // TODO: when we have better auditing and provider merge/verification mechanisms look at opening up patient write access to data.
            if ($restRequest->isPatientWriteRequest() && $restRequest->getRequestUserRole() == 'patient') {
                // not allowing patient userrole write for fhir
                throw new AccessDeniedException("patient", "demo", "Patient user role is not allowed to write FHIR resources.");
            }
        } elseif (($restRequest->isStandardApiRequest()) || ($restRequest->isPortalRequest())) {
            // don't do any checks on our open non-fhir resources
            if (
                $restRequest->getResource() == 'version'
                || $restRequest->getResource() == 'product'
                || $restRequest->isLocalApi() // skip security check if its a local api
            ) {
                return $event;
            }
            // ensure correct user role type for the non-fhir routes
            if (($restRequest->isStandardApiRequest()) && (($restRequest->getRequestUserRole() !== 'users') || ($scopeType !== 'user'))) {
                // TODO: should we allow system role to access oemr api?
                // TODO: @adunsulag need to figure out if there is a better ACL section for this
                throw new AccessDeniedException("patient", "demo", "not allowing patient or system role to access oemr api");
            }
            if (($restRequest->isPortalRequest()) && (($restRequest->getRequestUserRole() !== 'patient') || ($scopeType !== 'patient'))) {
                throw new AccessDeniedException("patient", "demo", "not allowing non-patient role to access port api");
            }
        } else {
            throw new AccessDeniedException("patient", "demo", "not allowing invalid role");
        }
        if (empty($event->getResource())) {
            $scope = $scopeType;
        } else {
            // Resource scope check
            $scope = $scopeType . '/' . $event->getResource() . '.' . $event->getPermission();
        }
        // check access token scopes
        if (!$restRequest->requestHasScope($scope)) {
            throw new AccessDeniedException($scopeType, $restRequest->getResource(), "scope not in access token", ['scope' => $scope, 'scopes_granted' => $restRequest->getAccessTokenScopes()]);
        }
        return $event;
    }

    public function addAuthorizationStrategy(IAuthorizationStrategy $strategy): void
    {
        if (!isset($this->authorizationStrategies)) {
            // Initialize the authorization strategies if not already set.
            $this->authorizationStrategies = [];
        }
        // This method is intended to add an authorization strategy.
        // Implementation details would depend on the specific requirements of the application.
        $this->authorizationStrategies[] = $strategy;
    }

    public function clearAuthorizationStrategies(): void
    {
        // This method is intended to clear all authorization strategies.
        // Implementation details would depend on the specific requirements of the application.
        $this->authorizationStrategies = [];
    }

    private function fhirRestRequestSkipSecurityCheck(HttpRestRequest $restRequest): bool
    {
        // if someone is hitting the local api and have a valid CSRF token we skip the security check.
        // TODO: @adunsulag need to verify this assumption is correct
        if ($restRequest->isLocalApi()) {
            return true;
        }

        $resource = $restRequest->getResource();
        // capability statement, smart well knowns, and operation definitions are skipped.
        $skippedChecks = ['metadata', '.well-known', 'OperationDefinition'];
        return in_array($resource, $skippedChecks);
    }

    private function shouldProcessRequest(Request $request): bool
    {
        foreach ($this->getAuthorizationStrategies() as $strategy) {
            if ($strategy->shouldProcessRequest($request)) {
                return true;
            }
        }
        return false;
    }
    private function authorizeRequest(Request $request): bool
    {
        foreach ($this->getAuthorizationStrategies() as $strategy) {
            if ($strategy->shouldProcessRequest($request)) {
                // throws UnauthorizedHttpException if the request is not authorized
                if ($strategy->authorizeRequest($request)) {
                    return true;
                }
            }
        }

        // TODO: @adunsulag need to verify this logic, do we want Bearer to be the default strategy?
        // If no strategy authorizes the request, deny by default
        throw new UnauthorizedHttpException("Bearer", "Authorization failed for the request.");
    }
}
