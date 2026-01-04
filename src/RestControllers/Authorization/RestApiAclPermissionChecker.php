<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Usage:
 *   RestApiAclPermissionChecker::
 */
class RestApiAclPermissionChecker
{
    use SingletonTrait;

    public function check(
        HttpRestRequest $request,
        string $section,
        string $value,
        string $aclPermission = '',
    ): void {
        if (AclMain::aclCheckCore(
            $section,
            $value,
            $request->getSession()->get("authUser"),
            $aclPermission,
        )) {
            return;
        }

        throw new AccessDeniedHttpException("Organization policy does not have permit access resource");
    }
}
