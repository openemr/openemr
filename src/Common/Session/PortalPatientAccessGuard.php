<?php

/**
 * Authorize patient demographic access shared by patient-portal and staff flows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;

final class PortalPatientAccessGuard
{
    /**
     * Assert that the active session may read the selected patient.
     */
    public static function assertCanRead(mixed $patientPid): void
    {
        self::assertAccess($patientPid);
    }

    /**
     * Assert that the active session may update the selected patient.
     */
    public static function assertCanWrite(mixed $patientPid): void
    {
        self::assertAccess($patientPid, true);
    }

    /**
     * Pure predicate for the common staff ACL decision.
     */
    public static function isStaffAccessAllowed(bool $hasPortalAccess, bool $hasDemographicsAccess): bool
    {
        return $hasPortalAccess && $hasDemographicsAccess;
    }

    /**
     * Enforce portal ownership or the required clinic staff ACLs.
     */
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
        if (!self::isStaffAccessAllowed(
            AclMain::aclCheckCore('patientportal', 'portal'),
            AclMain::aclCheckCore('patients', 'demo', '', $accessType),
        )) {
            AccessDeniedHelper::deny('Portal demographics review access denied');
        }
    }
}
