<?php

namespace OpenEMR\RestControllers\Config;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Authorization\RestApiAclPermissionChecker;

/**
 * @deprecated Use RestApiAclPermissionChecker::getInstance()->check() instead
 */
class RestConfig
{
    public function request_authorization_check(HttpRestRequest $request, $section, $value, $aclPermission = ''): void
    {
        RestApiAclPermissionChecker::getInstance()->check($request, $section, $value, $aclPermission);
    }
}
