<?php

namespace OpenEMR\RestControllers\Finder;

use OpenEMR\Common\Http\HttpRestRequest;

interface IRouteFinder
{
    /**
     * Finds routes based on the provided HTTP request.
     * @param HttpRestRequest $request
     * @return array
     */
    function find(HttpRestRequest $request): array;
}
