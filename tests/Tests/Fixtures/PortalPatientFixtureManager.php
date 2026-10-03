<?php

/**
 * PortalPatientFixtureManager
 *
 * Creates and removes patient records that also carry portal-login credentials
 * so tests can exercise portal-authenticated flows (patient portal login,
 * OAuth2 password grant with user_role=patient, portal API endpoints, etc.).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Uuid;

class PortalPatientFixtureManager
{
    /** Prefix so tests can find + clean fixture rows without touching real data. */
    public const FIXTURE_PUBPID_PREFIX = 'test-fixture-portal-';

    /**
     * Install a portal-enabled patient with a known plaintext password.
     *
     * Creates a `patient_data` row (with `allow_patient_portal = YES`) and a
     * companion `patient_access_onsite` row so both the portal browser login
     * and the OAuth2 password grant (user_role=patient) can authenticate as
     * this patient.
     *
     * @return array{pid: int, pubpid: non-falsy-string, portal_username: string, portal_login_username: string, plain_password: string, email: string}
     */
    public function installPortalPatient(
        string $portalLoginUsername,
        string $plainPassword,
        string $email = 'portal-fixture@example.com',
        string $fname = 'Portal',
        string $lname = 'Fixture',
    ): array {
        $pubpid = self::FIXTURE_PUBPID_PREFIX . Uuid::uuid4()->toString();
        $pid = $this->allocateNextPid();
        $uuid = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();

        QueryUtils::sqlInsert(
            "INSERT INTO patient_data SET pid = ?, pubpid = ?, uuid = ?, fname = ?, lname = ?, "
                . "email = ?, allow_patient_portal = 'YES', DOB = '1970-01-01', date = NOW()",
            [$pid, $pubpid, $uuid, $fname, $lname, $email]
        );

        // AuthHash::passwordHash takes by reference; hand it a scratch copy
        // so the by-ref taint does not drop $plainPassword back to mixed.
        $scratch = $plainPassword;
        $hash = (new AuthHash())->passwordHash($scratch);
        QueryUtils::sqlInsert(
            "INSERT INTO patient_access_onsite SET pid = ?, portal_username = ?, "
                . "portal_login_username = ?, portal_pwd = ?, portal_pwd_status = 1",
            [$pid, $portalLoginUsername, $portalLoginUsername, $hash]
        );

        return [
            'pid'                   => $pid,
            'pubpid'                => $pubpid,
            'portal_username'       => $portalLoginUsername,
            'portal_login_username' => $portalLoginUsername,
            'plain_password'        => $plainPassword,
            'email'                 => $email,
        ];
    }

    /**
     * Remove every portal-patient fixture row (any patient whose pubpid begins
     * with FIXTURE_PUBPID_PREFIX). Safe to call in tearDown even when nothing
     * was installed in the current test.
     */
    public function removePortalPatientFixtures(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT pid, uuid FROM patient_data WHERE pubpid LIKE ?",
            [self::FIXTURE_PUBPID_PREFIX . '%']
        );
        foreach ($rows as $row) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM patient_access_onsite WHERE pid = ?",
                [$row['pid']]
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?",
                [$row['uuid']]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM patient_data WHERE pubpid LIKE ?",
            [self::FIXTURE_PUBPID_PREFIX . '%']
        );
    }

    private function allocateNextPid(): int
    {
        $row = QueryUtils::querySingleRow("SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data");
        $next = $row['next_pid'] ?? 1;
        return is_numeric($next) ? (int) $next : 1;
    }
}
