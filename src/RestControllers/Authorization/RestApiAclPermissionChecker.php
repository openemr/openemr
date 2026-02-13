<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025-2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Usage:
 *   RestApiAclPermissionChecker::getInstance()->check($request, 'admin', 'users')
 *   RestApiAclPermissionChecker::getInstance()->check($request, 'patients', 'docs', ['write','addonly'])
 */
class RestApiAclPermissionChecker
{
    use SingletonTrait;

    public function check(
        HttpRestRequest $request,
        string $section,
        string $value,
        string|array $aclPermission = '',
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
