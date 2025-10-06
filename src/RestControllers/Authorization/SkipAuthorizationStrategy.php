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
     * @var string[] List of routes to skip authorization for
     */
    private array $skipRoutes = [];

    private bool $skipOptionsMethod = true;

    private ?UserService $userService = null;

    public function getUserService(): UserService
    {
        if (!isset($this->userService)) {
            $this->userService = new UserService();
        }
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
            if (str_starts_with($route, $pathInfo)) {
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
