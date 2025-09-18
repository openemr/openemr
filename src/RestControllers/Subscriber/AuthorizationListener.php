<?php

/**
 * AuthorizationListener is currently functioning as the Policy Decision Point (PEP) for the OpenEMR REST API.
 * There are currently two Policy Enforcement Points (PEP) in the rest API flow sequence in OpenEMR
 * 1. The onKernelRequest which is the first PEP that checks the request and authorizes it based on the defined authorization strategies.
 * 2. The onRestApiSecurityCheck which is the second PEP that checks the request and authorizes it based upon the access token scopes.
 *
 * We probably need to refactor this in the future to have a more robust policy decision point (PDP) and policy enforcement point (PEP) system.
 * This would allow for more flexibility in the authorization process and would allow for more complex authorization scenarios.
 *
 *
 */

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\RestControllers\Authorization\IAuthorizationStrategy;
use OpenEMR\RestControllers\Authorization\LocalApiAuthorizationController;
use OpenEMR\RestControllers\Authorization\SkipAuthorizationStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthorizationListener implements EventSubscriberInterface
{
    private SystemLogger $logger;
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 50]],
            RestApiSecurityCheckEvent::EVENT_HANDLE => [['onRestApiSecurityCheck', 50]]
        ];
    }
    private array $authorizationStrategies;

    private OEGlobalsBag $globalsBag;

    public function __construct()
    {
    }
    public function setGlobals(OEGlobalsBag $globalsBag): void
    {
        // This method is intended to set the globals bag for the authorization listener.
        $this->globalsBag = $globalsBag;
    }

    public function getGlobalsBag(): OEGlobalsBag
    {
        // This method is intended to return the globals bag for the authorization listener.
        if (!isset($this->globalsBag)) {
            $this->globalsBag = new OEGlobalsBag();
        }
        return $this->globalsBag;
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
            $this->addAuthorizationStrategy(new LocalApiAuthorizationController($this->getLogger(), $this->getGlobalsBag()));
            $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
            $skipAuthorizationStrategy->setSystemLogger($this->getLogger());
            $skipAuthorizationStrategy->addSkipRoute('/fhir/metadata');
            $skipAuthorizationStrategy->addSkipRoute('/fhir/.well-known/smart-configuration');
            $skipAuthorizationStrategy->addSkipRoute('/fhir/OperationDefinition');
            $skipAuthorizationStrategy->addSkipRoute('/api/version');
            $skipAuthorizationStrategy->addSkipRoute('/api/product');
            $this->addAuthorizationStrategy($skipAuthorizationStrategy);
            // TODO: @adunsulag not sure I like instantiating the ServerConfig here, perhaps we need to do this in a different way?
            $serverConfig = new ServerConfig();
            $bearerTokenAuthorizationStrategy = new BearerTokenAuthorizationStrategy($this->getGlobalsBag(), EventAuditLogger::instance(), $this->getLogger());
            $bearerTokenAuthorizationStrategy->setPublicKey($serverConfig->getPublicRestKey());
            $this->addAuthorizationStrategy($bearerTokenAuthorizationStrategy);
        }
        // This method is intended to return the list of authorization strategies.
        // Implementation details would depend on the specific requirements of the application.
        return $this->authorizationStrategies;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->getKernel() instanceof OEHttpKernel) {
            // We only want to process this if the kernel is an OEHttpKernel.
            return;
        } else {
            $this->setLogger($event->getKernel()->getSystemLogger());
            $this->setGlobals($event->getKernel()->getGlobalsBag());
        }

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

        // SkipAuthorizationStrategy or LocalApiAuthorizationController will set these properties and we can skip further checks.
        // TODO: @adunsulag should we just use skipAuthorization instead of isLocalApi?
        if ($restRequest->isLocalApi() || $restRequest->attributes->get('skipAuthorization', false) === true) {
            // If the request has been marked to skip authorization, we return the event without further checks.
            $this->getLogger()->debug("Skipping authorization for request", ['request' => $restRequest]);
            return $event;
        }

        // we check the request type against the user role... this is fairly cumbersome and complicated
        // TODO: @adunsulag can we look at simplifying this role / request type check?
        if ($restRequest->isFhir()) {
            // we do NOT want logged in patients writing data at this point so we fail
            // TODO: when we have better auditing and provider merge/verification mechanisms look at opening up patient write access to data.
            if ($restRequest->isPatientWriteRequest() && $restRequest->getRequestUserRole() == 'patient') {
                // not allowing patient userrole write for fhir
                throw new AccessDeniedException("patient", "demo", "Patient user role is not allowed to write FHIR resources.");
            }
        } elseif (($restRequest->isStandardApiRequest()) || ($restRequest->isPortalRequest())) {
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
        $scopeEntity = ScopeEntity::createFromString($scope);
        if (!$restRequest->requestHasScopeEntity($scopeEntity)) {
            throw new AccessDeniedException($scopeType, $restRequest->getResource() ?? '', "scope " . $scope . " not in access token");
        }
        $this->updateRequestWithConstraints($request, $scopeEntity);
        return $event;
    }

    /**
     * Updates the request query parameters with constraints from ScopeEntity objects that match the given scope
     *
     * @param HttpRestRequest $request The request to update, will have query parameters modified if there are granular constraints
     * @param ScopeEntity $scope The scope to match against
     * @return void
     */
    private function updateRequestWithConstraints(HttpRestRequest $request, ScopeEntity $scope): void
    {
        $scopeEntities = $request->getAllContainedScopesForScopeEntity($scope);
        $constraints = [];

        foreach ($scopeEntities as $scopeEntity) {
            // Check if this scope entity matches or is contained by the given scope
            $scope->addScopePermissions($scopeEntity);
        }
        $constraints = $scope->getPermissions()->getConstraints();
        if (!empty($constraints)) {
            // Merge constraints with existing query parameters
            $request->query->add($constraints);
            $logValues = [
                'scope' => $scope->getIdentifier(),
                'constraints' => $constraints,
                'mergedQuery' => $request->query->all()
            ];
            $this->getLogger()->debug("Updated request with scope constraints", $logValues);
        }
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
