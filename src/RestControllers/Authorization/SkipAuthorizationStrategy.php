<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\HttpFoundation\Request;

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
        return true;
    }
}
