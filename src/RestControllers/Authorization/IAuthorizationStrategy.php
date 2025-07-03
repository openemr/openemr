<?php

namespace OpenEMR\RestControllers\Authorization;

use Symfony\Component\HttpFoundation\Request;

// TODO: @adunsulag look at renaming this to be IAuthenticationStrategy or similar, as this is more about authentication than authorization.
interface IAuthorizationStrategy
{

    /**
     * Determines if the request should be processed by this authorization strategy.
     *
     * @param Request $request
     * @return bool
     */
    public function shouldProcessRequest(Request $request): bool;

    /**
     * Authorizes the request based on the strategy's rules.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizeRequest(Request $request): bool;
}
