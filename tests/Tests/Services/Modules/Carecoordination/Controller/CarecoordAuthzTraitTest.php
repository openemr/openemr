<?php

/**
 * Covers CarecoordAuthzTrait::isPatientAuthorized() across the three
 * decision axes it exposes:
 *   - caller holds `patients/docs` → authorized for any valid pid
 *   - caller lacks the ACL but is scoped to the same patient via session
 *   - caller lacks both → denied
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
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CarecoordAuthzTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove('pid');
        $session->remove('authUser');
        $session->remove('authUserID');
        $session->remove('authProvider');
    }

    public function testInvalidPidIsAlwaysRejected(): void
    {
        $harness = $this->makeHarness();
        $this->assertFalse(self::callAuthz($harness, 0));
        $this->assertFalse(self::callAuthz($harness, -1));
    }

    public function testCallerWithPatientsDocsAclIsAuthorizedForAnyPatient(): void
    {
        $harness = $this->makeHarness();
        // admin / super holds patients/docs by default in a fresh demo install.
        $this->loginAsAdmin();
        $this->assertTrue(self::callAuthz($harness, 1));
        $this->assertTrue(self::callAuthz($harness, 999));
    }

    public function testCallerWithoutAclAndNoSessionPidIsRejected(): void
    {
        $harness = $this->makeHarness();
        // No login → no session identity, no ACL — call must return false.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove('authUser');
        $session->remove('authUserID');
        $session->remove('pid');
        $this->assertFalse(self::callAuthz($harness, 1));
    }

    public function testCallerWithoutAclButMatchingSessionPidIsAuthorized(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('pid', 42);
        $this->assertTrue(self::callAuthz($harness, 42));
    }

    public function testCallerWithoutAclAndMismatchedSessionPidIsRejected(): void
    {
        $harness = $this->makeHarness();
        $this->loginAsNonAclHolder();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('pid', 42);
        $this->assertFalse(self::callAuthz($harness, 43));
    }

    private function makeHarness(): object
    {
        return new class {
            use CarecoordAuthzTrait {
                isPatientAuthorized as public;
            }
        };
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

    private static function callAuthz(object $instance, int $pid): bool
    {
        $m = new ReflectionMethod($instance, 'isPatientAuthorized');
        return (bool) $m->invoke($instance, $pid);
    }
}
