<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Http\HttpRestRequest;

// TODO: @adunsulag look at renaming this to be IAuthenticationStrategy or similar, as this is more about authentication than authorization.
interface IAuthorizationStrategy
{
    /**
     * Determines if the request should be processed by this authorization strategy.
     *
     * @param HttpRestRequest $request
     * @return bool
     */
    public function shouldProcessRequest(HttpRestRequest $request): bool;

    /**
     * Authorizes the request based on the strategy's rules.
     *
     * @param HttpRestRequest $request
     * @return bool
     */
    public function authorizeRequest(HttpRestRequest $request): bool;
}
