<?php

/**
 * Covers CarecoordAuthzTrait, exercising both of the trait's helpers:
 *
 *   isPatientAuthorized() across three decision axes:
 *     - caller holds `patients/docs` → authorized for any valid pid
 *     - caller lacks the ACL but is scoped to the same patient via session
 *     - caller lacks both → denied
 *
 *   isAuditMasterAccessible() across its two row shapes:
 *     - bound-pid rows gate on patient access to the stored pid; a claimed
 *       pid that disagrees with the stored pid is rejected
 *     - pre-approval staging rows (storedPid === 0) gate on the shared
 *       patients/docs ACL plus a per-patient check on claimedPid when the
 *       caller is binding one
 *
 * The trait is loaded under the Zend module autoloader at test time
 * (services suite boots the full application), so the tests live under
 * tests/Tests/Services/Modules/Carecoordination/.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\CareCoordination\Controller;

use Carecoordination\Controller\CarecoordAuthzTrait;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CarecoordAuthzTraitTest extends TestCase
{
    /** @var list<int> */
    private array $auditMasterIds = [];

    protected function tearDown(): void
    {
        foreach ($this->auditMasterIds as $amid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM audit_master WHERE id = ?", [$amid]);
        }
        $this->auditMasterIds = [];

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove('pid');
        $session->remove('authUser');
        $session->remove('authUserID');
        $session->remove('authProvider');
    }

    public function testInvalidPidIsAlwaysRejected(): void
    {
        $harness = $this->makeHarness();
        $this->assertFalse(self::callPatientAuthz($harness, 0));
        $this->assertFalse(self::callPatientAuthz($harness, -1));
    }

    public function testCallerWithPatientsDocsAclIsAuthorizedForAnyPatient(): void
    {
        $harness = $this->makeHarness();
        // admin / super holds patients/docs by default in a fresh demo install.
        $this->loginAsAdmin();
        $this->assertTrue(self::callPatientAuthz($harness, 1));
        $this->assertTrue(self::callPatientAuthz($harness, 999));
    }

    public function testCallerWithoutAclAndNoSessionPidIsRejected(): void
    {
        $harness = $this->makeHarness();
        // No login → no session identity, no ACL — call must return false.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove('authUser');
        $session->remove('authUserID');
        $session->remove('pid');
        $this->assertFalse(self::callPatientAuthz($harness, 1));
    }

    public function testCallerWithoutAclButMatchingSessionPidIsAuthorized(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('pid', 42);
        $this->assertTrue(self::callPatientAuthz($harness, 42));
    }

    public function testCallerWithoutAclAndMismatchedSessionPidIsRejected(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('pid', 42);
        $this->assertFalse(self::callPatientAuthz($harness, 43));
    }

    public function testIsAuditMasterAccessibleRejectsInvalidAmid(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        $this->assertFalse(self::callAuditAuthz($harness, 0));
        $this->assertFalse(self::callAuditAuthz($harness, -5));
    }

    public function testIsAuditMasterAccessibleRejectsUnknownAmid(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        // 0x7FFFFFFF — well past any audit_master.id that could exist.
        $this->assertFalse(self::callAuditAuthz($harness, 2147483647));
    }

    public function testBoundAuditRowAuthorizesCallerWithPatientAccess(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        $amid = $this->insertAuditRow(storedPid: 42);
        $this->assertTrue(self::callAuditAuthz($harness, $amid));
        $this->assertTrue(self::callAuditAuthz($harness, $amid, 42));
    }

    public function testBoundAuditRowRejectsMismatchedClaimedPid(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        $amid = $this->insertAuditRow(storedPid: 42);
        // A claimed pid other than the stored pid must be refused even when
        // the caller holds patients/docs for the real stored pid.
        $this->assertFalse(self::callAuditAuthz($harness, $amid, 43));
    }

    public function testBoundAuditRowRejectsCallerWithoutPatientAccess(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $amid = $this->insertAuditRow(storedPid: 42);
        $this->assertFalse(self::callAuditAuthz($harness, $amid));
    }

    public function testPreApprovalRowAllowsAclHolderForListAndDiscard(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        $amid = $this->insertAuditRow(storedPid: 0);
        $this->assertTrue(self::callAuditAuthz($harness, $amid));
        $this->assertTrue(self::callAuditAuthz($harness, $amid, 0));
    }

    public function testPreApprovalRowRequiresPatientAccessForClaimedPid(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsAdmin();
        $amid = $this->insertAuditRow(storedPid: 0);
        // Admin holds patients/docs so any claimed pid is authorized.
        $this->assertTrue(self::callAuditAuthz($harness, $amid, 42));
    }

    public function testPreApprovalRowDeniedWithoutAcl(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $amid = $this->insertAuditRow(storedPid: 0);
        // No patients/docs ACL means the pre-approval row is unreachable
        // regardless of what claimedPid the caller supplies.
        $this->assertFalse(self::callAuditAuthz($harness, $amid));
        $this->assertFalse(self::callAuditAuthz($harness, $amid, 42));
    }

    private function makeHarness(): object
    {
        return new class {
            use CarecoordAuthzTrait {
                isPatientAuthorized as public;
                isAuditMasterAccessible as public;
            }
        };
    }

    private function insertAuditRow(int $storedPid): int
    {
        $amid = (int) QueryUtils::sqlInsert(
            "INSERT INTO audit_master "
            . "SET pid = ?, approval_status = 1, ip_address = '', type = 1, "
            . "is_qrda_document = 0, is_unstructured_document = 0",
            [$storedPid]
        );
        $this->auditMasterIds[] = $amid;
        return $amid;
    }

    private function loginAsAdmin(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        $session->set('authUserID', 1);
        $session->set('authProvider', 'Default');
    }

    private function loginAsNonAclHolder(): void
    {
        // A placeholder user id that doesn't resolve to any ACL group; the
        // trait's AclMain::aclCheckCore call will deny `patients/docs` and
        // leave the decision to the session-pid branch.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'noaclusertest');
        $session->set('authUserID', 999999);
        $session->set('authProvider', '');
    }

    private static function callPatientAuthz(object $instance, int $pid): bool
    {
        $m = new ReflectionMethod($instance, 'isPatientAuthorized');
        return (bool) $m->invoke($instance, $pid);
    }

    private static function callAuditAuthz(object $instance, int $amid, ?int $claimedPid = null): bool
    {
        $m = new ReflectionMethod($instance, 'isAuditMasterAccessible');
        return (bool) $m->invoke($instance, $amid, $claimedPid);
    }
}
