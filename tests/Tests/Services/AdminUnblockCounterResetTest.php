<?php

/**
 * AdminUnblockCounterResetTest
 *
 * Regression coverage for the AuthUtils reset methods that back the admin
 * unblock UI added in the follow-up to #14110 (issue #14187):
 *
 *   1. AuthUtils::resetMfaUserFailCounter($username) — clears
 *      users_secure.mfa_fail_counter + mfa_last_fail unconditionally
 *      (called from usergroup_admin.php via login_counter_ip_tracker.php's
 *      resetMfaFailCounter handler).
 *   2. AuthUtils::resetMfaIpCounter($ipId) — clears
 *      ip_tracking.mfa_login_fail_counter + mfa_last_login_fail by
 *      primary key (called from ip_tracker.php via
 *      login_counter_ip_tracker.php's resetIpMfaCounter handler).
 *   3. AuthUtils::resetPortalAccountFailedCounter($portalLoginUsername) —
 *      clears patient_access_onsite.portal_fail_counter + portal_last_fail
 *      (called from portal_lockout_tracker.php via
 *      login_counter_ip_tracker.php's resetPortalAccountCounter handler).
 *
 * Each test seeds a row above the corresponding lockout threshold, calls
 * the reset method, and verifies the counter zeroes AND the paired
 * timestamp column is nulled. tearDown restores prior state so shared
 * rows (e.g. admin, 127.0.0.1) are not left mutated.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AdminUnblockCounterResetTest extends TestCase
{
    private ?PortalPatientFixtureManager $portalFixtures = null;

    /**
     * users_secure snapshots keyed by username; see PasswordGrantHardeningTest
     * for the same pattern. Restores exact prior mfa_fail_counter/mfa_last_fail
     * so a shared user (admin) doesn't lose real state to the test.
     *
     * @var array<string, array<mixed>>
     */
    private array $originalUserLockoutByUsername = [];
    /**
     * ip_tracking snapshots keyed by ip_string. Value is the row if it existed
     * before the test, or null if not (tearDown deletes anything we inserted).
     *
     * @var array<string, ?array<mixed>>
     */
    private array $originalIpTrackingByString = [];
    /**
     * Snapshots of mutated globals so tearDown can restore whatever value the
     * shared runtime had before the test. Keyed by global name.
     *
     * @var array<string, mixed>
     */
    private array $originalGlobals = [];

    protected function tearDown(): void
    {
        foreach ($this->originalUserLockoutByUsername as $username => $original) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `login_fail_counter` = ?, "
                    . "`last_login_fail` = ?, `auto_block_emailed` = ?, "
                    . "`mfa_fail_counter` = ?, `mfa_last_fail` = ? "
                    . "WHERE BINARY `username` = ?",
                [
                    $original['login_fail_counter'],
                    $original['last_login_fail'],
                    $original['auto_block_emailed'],
                    $original['mfa_fail_counter'],
                    $original['mfa_last_fail'],
                    $username,
                ]
            );
        }
        foreach ($this->originalIpTrackingByString as $ipString => $original) {
            if ($original === null) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM `ip_tracking` WHERE `ip_string` = ?",
                    [$ipString]
                );
                continue;
            }
            QueryUtils::sqlStatementThrowException(
                "UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = ?, "
                    . "`ip_login_fail_counter` = ?, `ip_last_login_fail` = ?, "
                    . "`ip_auto_block_emailed` = ?, "
                    . "`mfa_login_fail_counter` = ?, `mfa_last_login_fail` = ? "
                    . "WHERE `ip_string` = ?",
                [
                    $original['total_ip_login_fail_counter'],
                    $original['ip_login_fail_counter'],
                    $original['ip_last_login_fail'],
                    $original['ip_auto_block_emailed'],
                    $original['mfa_login_fail_counter'],
                    $original['mfa_last_login_fail'],
                    $ipString,
                ]
            );
        }

        $this->portalFixtures?->removePortalPatientFixtures();

        // Restore mutated globals so tests exercising collectIpLoginFailsSql
        // don't leak lockout-threshold values into unrelated tests running in
        // the same phpunit process.
        $globals = OEGlobalsBag::getInstance();
        foreach ($this->originalGlobals as $key => $value) {
            $globals->set($key, $value);
        }

        parent::tearDown();
    }

    // ---------- resetMfaUserFailCounter ----------

    public function testResetMfaUserFailCounterZerosCounterAndClearsTimestamp(): void
    {
        $username = 'admin';
        $this->snapshotUserLockout($username);

        QueryUtils::sqlStatementThrowException(
            "UPDATE `users_secure` SET `mfa_fail_counter` = 7, `mfa_last_fail` = NOW() "
                . "WHERE BINARY `username` = ?",
            [$username]
        );
        $this->assertSame(7, $this->readMfaUserCounter($username), 'Seed must set the counter to 7');
        $this->assertNotNull(
            $this->readMfaUserLastFail($username),
            'Seed must set mfa_last_fail'
        );

        AuthUtils::resetMfaUserFailCounter($username);

        $this->assertSame(
            0,
            $this->readMfaUserCounter($username),
            'resetMfaUserFailCounter must zero users_secure.mfa_fail_counter'
        );
        $this->assertNull(
            $this->readMfaUserLastFail($username),
            'resetMfaUserFailCounter must null users_secure.mfa_last_fail'
        );
    }

    public function testResetMfaUserFailCounterIsNoOpForUnknownUsername(): void
    {
        // Unknown user has no row; the UPDATE affects zero rows and no
        // exception must be raised (admin unblock UI can still fire the
        // handler even if a race deleted the row in the meantime).
        $unknown = 'nonexistent-user-' . Uuid::uuid4()->toString();
        AuthUtils::resetMfaUserFailCounter($unknown);
        $this->assertSame(0, $this->readMfaUserCounter($unknown));
    }

    // ---------- resetMfaIpCounter ----------

    public function testResetMfaIpCounterZerosCounterAndClearsTimestampByRowId(): void
    {
        $ipString = 'test-admin-unblock-ip-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 9, NOW())",
            [$ipString]
        );
        $ipId = $this->readIpTrackingId($ipString);
        $this->assertGreaterThan(0, $ipId, 'Seed row must be present');
        $this->assertSame(9, $this->readMfaIpCounter($ipString), 'Seed must set the counter to 9');
        $this->assertNotNull($this->readMfaIpLastFail($ipString), 'Seed must set mfa_last_login_fail');

        AuthUtils::resetMfaIpCounter($ipId);

        $this->assertSame(
            0,
            $this->readMfaIpCounter($ipString),
            'resetMfaIpCounter must zero ip_tracking.mfa_login_fail_counter'
        );
        $this->assertNull(
            $this->readMfaIpLastFail($ipString),
            'resetMfaIpCounter must null ip_tracking.mfa_last_login_fail'
        );
    }

    public function testResetMfaIpCounterLeavesPasswordCounterUntouched(): void
    {
        // Admin unblock UI has separate Reset buttons per axis — clearing
        // the MFA counter must not zero the sibling password counter,
        // which may be actively lockout-ing a distinct brute force.
        $ipString = 'test-admin-unblock-ip-isolation-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `ip_login_fail_counter`, `ip_last_login_fail`, "
                . "`mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 5, NOW(), 9, NOW())",
            [$ipString]
        );
        $ipId = $this->readIpTrackingId($ipString);

        AuthUtils::resetMfaIpCounter($ipId);

        $this->assertSame(
            5,
            $this->readIpCounter($ipString),
            'resetMfaIpCounter must not touch the sibling ip_login_fail_counter'
        );
        $this->assertSame(0, $this->readMfaIpCounter($ipString));
    }

    // ---------- resetPortalAccountFailedCounter ----------

    public function testResetPortalAccountFailedCounterZerosCounterAndClearsTimestamp(): void
    {
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-unblock-' . Uuid::uuid4()->toString(),
            plainPassword: 'IrrelevantPassword1!'
        );

        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_access_onsite` SET `portal_fail_counter` = 6, "
                . "`portal_last_fail` = NOW() WHERE BINARY `portal_login_username` = ?",
            [$fixture['portal_login_username']]
        );
        $this->assertSame(
            6,
            $this->readPortalAccountCounter($fixture['portal_login_username']),
            'Seed must set the counter to 6'
        );
        $this->assertNotNull(
            $this->readPortalAccountLastFail($fixture['portal_login_username']),
            'Seed must set portal_last_fail'
        );

        AuthUtils::resetPortalAccountFailedCounter($fixture['portal_login_username']);

        $this->assertSame(
            0,
            $this->readPortalAccountCounter($fixture['portal_login_username']),
            'resetPortalAccountFailedCounter must zero patient_access_onsite.portal_fail_counter'
        );
        $this->assertNull(
            $this->readPortalAccountLastFail($fixture['portal_login_username']),
            'resetPortalAccountFailedCounter must null patient_access_onsite.portal_last_fail'
        );
    }

    public function testResetPortalAccountFailedCounterIsNoOpForEmptyUsername(): void
    {
        // Guards an empty-string username so the ajax handler's empty()
        // gate can be bypassed with a stray whitespace-only payload
        // without hitting a WHERE = '' UPDATE that could touch rows with
        // a genuinely-empty portal_login_username. Prove the reset was
        // a no-op by seeding a real row and asserting it is untouched.
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-empty-guard-' . Uuid::uuid4()->toString(),
            plainPassword: 'IrrelevantPassword1!'
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_access_onsite` SET `portal_fail_counter` = 4 "
                . "WHERE BINARY `portal_login_username` = ?",
            [$fixture['portal_login_username']]
        );

        AuthUtils::resetPortalAccountFailedCounter('');

        $this->assertSame(
            4,
            $this->readPortalAccountCounter($fixture['portal_login_username']),
            'Empty-username guard must not touch any real row'
        );
    }

    public function testResetPortalAccountFailedCounterIsNoOpForUnknownUsername(): void
    {
        $unknown = 'nonexistent-portal-user-' . Uuid::uuid4()->toString();
        AuthUtils::resetPortalAccountFailedCounter($unknown);
        $this->assertSame(0, $this->readPortalAccountCounter($unknown));
    }

    // ---------- resetMfaIpFailCounter(string) ----------

    public function testResetMfaIpFailCounterByStringZerosCounterAndClearsTimestamp(): void
    {
        // Success-path variant keyed by ip_string (as opposed to the by-id
        // resetMfaIpCounter that backs the admin UI). Previously private,
        // now public because both the success-path resetMfaChallengeCounters
        // caller and the admin UI callers now live outside the class.
        $ipString = 'test-mfa-ipstring-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 4, NOW())",
            [$ipString]
        );
        $this->assertSame(4, $this->readMfaIpCounter($ipString));
        $this->assertNotNull($this->readMfaIpLastFail($ipString));

        AuthUtils::resetMfaIpFailCounter($ipString);

        $this->assertSame(0, $this->readMfaIpCounter($ipString));
        $this->assertNull($this->readMfaIpLastFail($ipString));
    }

    // ---------- collectIpLoginFailsSql filter branches ----------
    // These tests exercise the SELECT that ip_tracker.php uses to list the
    // rows an admin can act on. They cover the new MFA columns in the
    // projection and each of the new filter branches (with/without window,
    // max=0 short-circuit, and the plain unfiltered case).

    public function testCollectIpLoginFailsSqlSelectsMfaColumns(): void
    {
        // Regression guard: the SELECT projection includes the new MFA
        // fields so ip_tracker.php can render the MFA columns without a
        // second round-trip to the DB.
        $ipString = 'test-mfa-select-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 3, NOW())",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(true, false, false),
            $ipString
        );

        $this->assertNotNull($row, 'showOnlyWithCount must return a row whose only non-zero counter is MFA');
        $this->assertSame(3, is_numeric($row['mfa_login_fail_counter'] ?? null) ? (int) $row['mfa_login_fail_counter'] : 0);
        $this->assertArrayHasKey('mfa_last_login_fail', $row);
        $this->assertArrayHasKey('seconds_mfa_last_login_fail', $row);
    }

    public function testCollectIpLoginFailsSqlShowOnlyAutoBlockedMatchesMfaAtBoundary(): void
    {
        // AuthUtils::isMfaChallengeBlocked() still treats the IP as blocked at
        // seconds == window; the filter must include those rows so admins can
        // reset them.
        $this->overrideGlobal('ip_max_failed_logins', 3);
        $this->overrideGlobal('ip_time_reset_password_max_failed_logins', 300);

        $ipString = 'test-mfa-window-boundary-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        // counter is exactly at max (>= satisfies the MFA predicate) and the
        // last-fail timestamp lands right inside the reset window.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 3, DATE_SUB(NOW(), INTERVAL 60 SECOND))",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(false, false, true),
            $ipString
        );
        $this->assertNotNull(
            $row,
            'MFA counter at exactly ip_max_failed_logins inside the reset window must appear in the auto-blocked filter'
        );
    }

    public function testCollectIpLoginFailsSqlShowOnlyAutoBlockedWithoutWindowIncludesMfaOverCap(): void
    {
        // No reset window configured — a row above the MFA cap is
        // permanently blocked, so the filter must return it regardless
        // of last-fail age.
        $this->overrideGlobal('ip_max_failed_logins', 2);
        $this->overrideGlobal('ip_time_reset_password_max_failed_logins', 0);

        $ipString = 'test-mfa-nowindow-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                . "VALUES (?, 5, DATE_SUB(NOW(), INTERVAL 1 DAY))",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(false, false, true),
            $ipString
        );
        $this->assertNotNull(
            $row,
            'MFA-over-cap row must appear when auto-block window is disabled'
        );
    }

    public function testCollectIpLoginFailsSqlShowOnlyAutoBlockedReturnsEmptyWhenMaxIsZero(): void
    {
        // ip_max_failed_logins = 0 disables auto-block entirely. Without the
        // 1 = 0 short-circuit the query would fall through to no predicate and
        // list every counter-active row (all labelled "No" by the renderer).
        $this->overrideGlobal('ip_max_failed_logins', 0);

        $ipString = 'test-max-zero-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `ip_login_fail_counter`, `mfa_login_fail_counter`) "
                . "VALUES (?, 99, 99)",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(false, false, true),
            $ipString
        );
        $this->assertNull(
            $row,
            'When ip_max_failed_logins is 0 the auto-blocked filter must return no rows'
        );
    }

    public function testCollectIpLoginFailsSqlShowOnlyManuallyBlockedIncludesMfaRow(): void
    {
        // Manual-block filter is orthogonal to the counter axes; a
        // force-blocked row must appear regardless of MFA counter state.
        $ipString = 'test-manual-block-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `ip_force_block`, `mfa_login_fail_counter`) "
                . "VALUES (?, 1, 0)",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(false, true, false),
            $ipString
        );
        $this->assertNotNull($row, 'force-blocked row must appear in the manual-block filter');
    }

    public function testCollectIpLoginFailsSqlUnfilteredReturnsRowWithMfaColumnsPopulated(): void
    {
        // Baseline: no filters. Row must appear and carry the MFA-column
        // additions in the projection.
        $ipString = 'test-unfiltered-' . Uuid::uuid4()->toString();
        $this->snapshotIpTracking($ipString);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`) VALUES (?, 0)",
            [$ipString]
        );

        $row = $this->findIpTrackingRow(
            AuthUtils::collectIpLoginFailsSql(false, false, false),
            $ipString
        );
        $this->assertNotNull($row);
        $this->assertArrayHasKey('mfa_login_fail_counter', $row);
        $this->assertArrayHasKey('mfa_last_login_fail', $row);
    }

    // ---------- helpers (mirrors PasswordGrantHardeningTest) ----------

    private function portalFixtures(): PortalPatientFixtureManager
    {
        return $this->portalFixtures ??= new PortalPatientFixtureManager();
    }

    private function snapshotUserLockout(string $username): void
    {
        if (array_key_exists($username, $this->originalUserLockoutByUsername)) {
            return;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `login_fail_counter`, `last_login_fail`, `auto_block_emailed`, "
                . "`mfa_fail_counter`, `mfa_last_fail` "
                . "FROM `users_secure` WHERE BINARY `username` = ?",
            [$username]
        );
        if (!is_array($row)) {
            return;
        }
        $this->originalUserLockoutByUsername[$username] = $row;
    }

    private function snapshotIpTracking(string $ipString): void
    {
        if (array_key_exists($ipString, $this->originalIpTrackingByString)) {
            return;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `total_ip_login_fail_counter`, `ip_login_fail_counter`, "
                . "`ip_last_login_fail`, `ip_auto_block_emailed`, "
                . "`mfa_login_fail_counter`, `mfa_last_login_fail` "
                . "FROM `ip_tracking` WHERE `ip_string` = ?",
            [$ipString]
        );
        $this->originalIpTrackingByString[$ipString] = is_array($row) ? $row : null;
    }

    private function readMfaUserCounter(string $username): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_fail_counter FROM users_secure WHERE BINARY username = ?",
            [$username]
        );
        $value = $row['mfa_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readMfaUserLastFail(string $username): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_last_fail FROM users_secure WHERE BINARY username = ?",
            [$username]
        );
        $value = $row['mfa_last_fail'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function readIpCounter(string $ipString): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT ip_login_fail_counter FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['ip_login_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readMfaIpCounter(string $ipString): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_login_fail_counter FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['mfa_login_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readMfaIpLastFail(string $ipString): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_last_login_fail FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['mfa_last_login_fail'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function readIpTrackingId(string $ipString): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT id FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['id'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readPortalAccountCounter(string $portalLoginUsername): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT portal_fail_counter FROM patient_access_onsite WHERE BINARY portal_login_username = ?",
            [$portalLoginUsername]
        );
        $value = $row['portal_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readPortalAccountLastFail(string $portalLoginUsername): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT portal_last_fail FROM patient_access_onsite WHERE BINARY portal_login_username = ?",
            [$portalLoginUsername]
        );
        $value = $row['portal_last_fail'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function overrideGlobal(string $key, mixed $value): void
    {
        $globals = OEGlobalsBag::getInstance();
        if (!array_key_exists($key, $this->originalGlobals)) {
            $this->originalGlobals[$key] = $globals->get($key);
        }
        $globals->set($key, $value);
    }

    /**
     * Iterate a collectIpLoginFailsSql result set (an ADORecordSet from
     * sqlStatement) and return the row whose ip_string matches, or null.
     *
     * @param false|\ADORecordSet $resultSet
     *
     * @return ?array<string, mixed>
     */
    private function findIpTrackingRow($resultSet, string $ipString): ?array
    {
        while (($row = QueryUtils::fetchArrayFromResultSet($resultSet)) !== false) {
            if (($row['ip_string'] ?? null) === $ipString) {
                /** @var array<string, mixed> $row */
                return $row;
            }
        }
        return null;
    }
}
