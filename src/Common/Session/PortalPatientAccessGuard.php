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
        self::assertAccess($patientPid, 'demo', '', 'Portal demographics review access denied');
    }

    /**
     * Assert that the active session may update the selected patient.
     */
    public static function assertCanWrite(mixed $patientPid): void
    {
        self::assertAccess($patientPid, 'demo', 'write', 'Portal demographics review access denied');
    }

    /**
     * Assert that the active session may read the selected patient's portal documents.
     */
    public static function assertCanReadDocuments(mixed $patientPid): void
    {
        self::assertAccess($patientPid, 'docs', '', 'Portal document access denied');
    }

    /**
     * Assert that the active session may add a portal document for the selected patient.
     * Staff with add-only document access may add, as they may upload a document.
     */
    public static function assertCanAddDocument(mixed $patientPid): void
    {
        self::assertAccess($patientPid, 'docs', ['write', 'addonly'], 'Portal document access denied');
    }

    /**
     * Assert that the active session may change or delete the selected patient's portal documents.
     */
    public static function assertCanWriteDocuments(mixed $patientPid): void
    {
        self::assertAccess($patientPid, 'docs', 'write', 'Portal document access denied');
    }

    /**
     * Pure predicate for the common staff ACL decision: the portal ACL plus the patient-level ACL
     * (demographics or documents) for the record.
     */
    public static function isStaffAccessAllowed(bool $hasPortalAccess, bool $hasPatientRecordAccess): bool
    {
        return $hasPortalAccess && $hasPatientRecordAccess;
    }

    /**
     * Enforce portal ownership or the required clinic staff ACLs.
     *
     * @param 'demo'|'docs' $patientsAcl the `patients` ACL the staff user needs for this record
     * @param string|list<string> $accessType '' for read, or the write access types accepted
     */
    private static function assertAccess(mixed $patientPid, string $patientsAcl, string|array $accessType, string $denial): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($session->has('patient_portal_onsite_two')) {
            PortalSessionPidGuard::assertOwnedBySession(
                $patientPid,
                PortalSessionPidGuard::requireBootstrapPid(),
            );
            return;
        }

        if (!self::isStaffAccessAllowed(
            AclMain::aclCheckCore('patientportal', 'portal'),
            AclMain::aclCheckCore('patients', $patientsAcl, '', $accessType),
        )) {
            AccessDeniedHelper::deny($denial);
        }
    }
}
