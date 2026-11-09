<?php

/**
 * StatementPortal: who a statement's portal notification goes to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Statement\StatementPortal;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class StatementPortalTest extends TestCase
{
    private const WITH_ACCOUNT_PID = 999941;
    private const NO_ACCOUNT_PID = 999942;
    private const NOT_ALLOWED_PID = 999943;
    private const BLANK_ACCOUNT_PID = 999944;
    private const PORTAL_USERNAME = 'stmt.portal.999941@example.test';

    private mixed $savedPortalUser = null;

    protected function setUp(): void
    {
        $this->cleanUp();
        $this->savedPortalUser = SessionWrapperFactory::getInstance()->getActiveSession()->get('portalUser');
        $this->addPatient(self::WITH_ACCOUNT_PID, 'YES', self::PORTAL_USERNAME);
        $this->addPatient(self::NO_ACCOUNT_PID, 'YES', null);
        $this->addPatient(self::NOT_ALLOWED_PID, 'NO', 'stmt.portal.999943@example.test');
        $this->addPatient(self::BLANK_ACCOUNT_PID, 'YES', '');
    }

    protected function tearDown(): void
    {
        if ($this->savedPortalUser === null) {
            SessionUtil::unsetSession('portalUser');
        } else {
            SessionUtil::setSession('portalUser', $this->savedPortalUser);
        }
        $this->cleanUp();
    }

    public function testOnlyAPatientWithAPortalAccountListsAsAPortalUser(): void
    {
        $portal = new StatementPortal(true, sys_get_temp_dir(), new Session(new MockArraySessionStorage()), null);

        $this->assertTrue($portal->listsAsPortalUser(self::WITH_ACCOUNT_PID));
        $this->assertFalse($portal->listsAsPortalUser(self::NO_ACCOUNT_PID));
        $this->assertFalse($portal->listsAsPortalUser(self::NOT_ALLOWED_PID));
        $this->assertFalse($portal->listsAsPortalUser(self::BLANK_ACCOUNT_PID));
    }

    public function testAuthorizeMakesTheStoredPortalUsernameTheSessionsPortalUser(): void
    {
        $this->assertTrue(StatementPortal::authorize(self::WITH_ACCOUNT_PID));

        $this->assertSame(self::PORTAL_USERNAME, SessionWrapperFactory::getInstance()->getActiveSession()->get('portalUser'));
        $this->assertFalse(StatementPortal::authorize(self::NO_ACCOUNT_PID));
    }

    private function addPatient(int $pid, string $allowPortal, ?string $portalUsername): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, allow_patient_portal) VALUES (?, ?, 'Pat', 'Zportal', '1950-01-01', 'Female', ?)",
            [$pid, (string) $pid, $allowPortal]
        );
        if ($portalUsername === null) {
            return;
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_access_onsite (pid, portal_username, portal_login_username, portal_pwd, portal_pwd_status) VALUES (?, ?, ?, 'x', 1)",
            [$pid, $portalUsername, $portalUsername]
        );
    }

    private function cleanUp(): void
    {
        foreach ([self::WITH_ACCOUNT_PID, self::NO_ACCOUNT_PID, self::NOT_ALLOWED_PID, self::BLANK_ACCOUNT_PID] as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_access_onsite WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$pid]);
        }
    }
}
