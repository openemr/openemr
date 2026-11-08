<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Services\UserService;

class SkipAuthorizationStrategy implements IAuthorizationStrategy
{
    use SystemLoggerAwareTrait;

    /**
     * @var string[] Routes to skip authorization for on an exact-path match only.
     */
    private array $skipRoutes = [];

    /**
     * @var string[] Routes to skip authorization for on an exact-path match or
     *               on a deeper child path (route + "/..."). Used for FHIR
     *               resource prefixes that have named operations under them,
     *               e.g. /fhir/OperationDefinition and
     *               /fhir/OperationDefinition/$bulkdata-status. Keep this list
     *               narrow — exact-match via addSkipRoute() is the default.
     */
    private array $skipRoutePrefixes = [];

    private bool $skipOptionsMethod = true;

    private ?UserService $userService = null;

    public function getUserService(): UserService
    {
        $this->userService ??= new UserService();
        return $this->userService;
    }

    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    public function shouldSkipOptionsMethod(bool $skipOptionsMethod): void
    {
        $this->skipOptionsMethod = $skipOptionsMethod;
    }

    public function addSkipRoute(string $route): void
    {
        $this->skipRoutes[] = $route;
    }

    /**
     * Register a route whose exact path AND any deeper child path should
     * skip authorization. Reserved for FHIR resource prefixes that have
     * named operations under them (e.g. /fhir/OperationDefinition and its
     * /fhir/OperationDefinition/$op children). Prefer addSkipRoute() for
     * everything else so a new skip entry does not accidentally expose a
     * sub-tree.
     */
    public function addSkipRoutePrefix(string $route): void
    {
        $this->skipRoutePrefixes[] = $route;
    }

    public function shouldProcessRequest(HttpRestRequest $request): bool
    {
        if ($request->getMethod() === 'OPTIONS' && $this->skipOptionsMethod) {
            return true;
        }
        $pathInfo = $request->getPathInfo();
        $sitePath = "/" . $request->getRequestSite();
        if (str_starts_with($pathInfo, $sitePath)) {
            $pathInfo = substr($pathInfo, strlen($sitePath));
        }
        foreach ($this->skipRoutes as $route) {
            if ($pathInfo === $route) {
                return true;
            }
        }
        // Prefix match: exact path OR any deeper child. Appending "/" when
        // probing the deeper-child case prevents a sibling like
        // "/fhir/OperationDefinition-fake" from matching the configured
        // "/fhir/OperationDefinition".
        foreach ($this->skipRoutePrefixes as $route) {
            $normalized = rtrim($route, '/');
            if ($pathInfo === $normalized || str_starts_with($pathInfo, $normalized . '/')) {
                return true;
            }
        }
        return false;
    }

    public function authorizeRequest(HttpRestRequest $request): bool
    {
        // No authorization needed for skipped routes
        $request->attributes->set('skipAuthorization', true);
        // if we have a session we can set the userId and tokenId attributes
        $session = $request->getSession();
        // userId is populated by the bearer token authorization strategy
        $userId = $session->get('authUserId');
        $userRole = UuidUserAccount::USER_ROLE_SYSTEM; // Default to system role if no userId is set
        if (!empty($userId)) {
            // TODO: how do we want to handle patient accounts?  This doesn't accommodate that.
            $userService = $this->getUserService();
            $user = $userService->getUser($userId);
            $userRole = UuidUserAccount::USER_ROLE_USERS;
            // Set the user in the request for HttpRestRequest
            $request->setRequestUser($user['uuid'], $user);
        }
        $request->setRequestUserRole($userRole);
        $request->attributes->set('userId', $userId);
        $request->attributes->set('clientId', null);
        $request->attributes->set('tokenId', null);
        $request->setRequestUserRole($userRole);
        return true;
    }
}
