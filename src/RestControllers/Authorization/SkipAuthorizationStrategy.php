<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SkipAuthorizationStrategy implements IAuthorizationStrategy
{
    /**
     * @var string[] List of routes to skip authorization for
     */
    private $skipRoutes = [];

    public function shouldSkipOptionsMethod(bool $skipOptionsMethod): void
    {
        $this->skipOptionsMethod = $skipOptionsMethod;
    }

    public function addSkipRoute(string $route): void
    {
        $this->skipRoutes[] = $route;
    }

    public function shouldProcessRequest(Request $request): bool
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

    public function authorizeRequest(Request $request): bool
    {
        // No authorization needed for skipped routes
        $request->attributes->set('skipAuthorization', true);
        // if we have a session we can set the userId and tokenId attributes
        $userId = $_SESSION['userId'] ?? null;
        $userRole = 'system'; // Default to system role if no userId is set
        if (!empty($userId)) {
            // TODO: look at abstracting this lines into a service as it duplicates LocalApiAuthorizationController
            $uuidToUser = new UuidUserAccount($userId);
            $user = $uuidToUser->getUserAccount();
            $userRole = $uuidToUser->getUserRole();
            if (empty($user)) {
                // unable to identify the users user role
                $this->logger->error("OpenEMR Error - api user account could not be identified, so forced exit", [
                    'userId' => $userId,
                    'userRole' => $uuidToUser->getUserRole()]);
                // TODO: @adunsulag shouldn't this be 500? if token is valid but user isn't found, seems like a system error as it never should happen
                throw new HttpException(400);
            }
            if (empty($userRole)) {
                // unable to identify the users user role
                $this->logger->error("OpenEMR Error - api user role for user could not be identified, so forced exit");
                // TODO: @adunsulag shouldn't this be 500? if token is valid but user role isn't found, seems like a system error as it never should happen
                throw new HttpException(400);
            }
            if ($request instanceof HttpRestRequest) {
                // Set the user in the request for HttpRestRequest
                $request->setRequestUser($user['uuid'], $user);
            }
        }
        $request->attributes->set('userId', $userId);
        $request->attributes->set('clientId', null);
        $request->attributes->set('tokenId', null);
        if ($request instanceof HttpRestRequest) {
            $request->setRequestUserRole($userRole);
        }
        return true;
    }
}
