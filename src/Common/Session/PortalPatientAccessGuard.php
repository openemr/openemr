<?php

/**
 * Authorize patient demographic access shared by patient-portal and staff flows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;

final class PortalPatientAccessGuard
{
    public static function assertCanRead(mixed $patientPid): void
    {
        self::assertAccess($patientPid);
    }

    public static function assertCanWrite(mixed $patientPid): void
    {
        self::assertAccess($patientPid, true);
    }

    private static function assertAccess(mixed $patientPid, bool $write = false): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($session->has('patient_portal_onsite_two')) {
            PortalSessionPidGuard::assertOwnedBySession(
                $patientPid,
                PortalSessionPidGuard::requireBootstrapPid(),
            );
            return;
        }

        $accessType = $write ? 'write' : '';
        if (
            !AclMain::aclCheckCore('patientportal', 'portal') ||
            !AclMain::aclCheckCore('patients', 'demo', '', $accessType)
        ) {
            AccessDeniedHelper::deny('Portal demographics review access denied');
        }
    }
}
