<?php

/**
 * PortalBootstrapCoreSessionRejectionIsolatedTest
 *
 * Coverage for the portal-bootstrap core-mode-fallback ACL check.
 *
 * The portal patient bootstrap at `portal/patient/_machine_config.php` has
 * historically fallen back to a core-user session when no portal-patient
 * session is present. Downstream portal controllers bind patient identity
 * via `bootstrap_pid` (set only for the portal-patient branch), so the
 * fallback branch let a core-user session traverse controllers such as
 * `OnsiteDocumentController::Query()` with an empty patient binding —
 * returning another patient's PHI when combined with the router's absence
 * of `p_acl` checks outside the patient-portal branch.
 *
 * Two changes are covered here:
 *   1. `_machine_config.php` requires the `patientportal/portal` ACL on
 *      the core-user fallback branch. Without that ACL the session is
 *      destroyed and the caller is bounced to the landing page — matching
 *      the "no authenticated user" behaviour.
 *   2. The router enforces each route's access rule for the core-user
 *      fallback as well as for portal patients. `All` and `Staff` routes are
 *      open to the fallback; `None` routes deny. That rule now lives in
 *      OpenEMR\Portal\Routing\PortalRouter and is tested by PortalRouterTest;
 *      this test keeps the route-table sentinel for the document routes.
 *
 * `_machine_config.php` is a legacy include-style script with
 * process-terminating side effects (`exit()`, header emission,
 * `interface/globals.php` require) so a real invocation is out of scope for
 * the isolated tier. Source-file text inspection matches the pattern used
 * elsewhere in this suite (see `FhirRouteAclEnforcementIsolatedTest`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\OnsiteDocumentController;
use OpenEMR\Portal\Routing\PortalRouteTable;
use OpenEMR\Portal\Routing\RouteAccess;
use PHPUnit\Framework\TestCase;

final class PortalBootstrapCoreSessionRejectionIsolatedTest extends TestCase
{
    private string $machineConfigContent = '';

    protected function setUp(): void
    {
        $machineConfigPath = realpath(__DIR__ . '/../../../../portal/patient/_machine_config.php');
        if (!is_string($machineConfigPath)) {
            $this->markTestSkipped('portal/patient/_machine_config.php not found');
        }
        $machineConfig = file_get_contents($machineConfigPath);
        if ($machineConfig === false) {
            $this->markTestSkipped('Failed to read _machine_config.php');
        }
        $this->machineConfigContent = $machineConfig;

    }

    // -------------------------------------------------------------------------
    // _machine_config.php — core-fallback ACL gate
    // -------------------------------------------------------------------------

    public function testMachineConfigImportsAclMain(): void
    {
        $this->assertStringContainsString(
            'use OpenEMR\\Common\\Acl\\AclMain;',
            $this->machineConfigContent,
            '_machine_config.php must import AclMain so the core-fallback ACL check can run',
        );
    }

    public function testMachineConfigChecksPatientportalPortalAclOnCoreFallback(): void
    {
        // The regex checks that the ACL section/subsection are the intended
        // pair — anyone changing the ACL to a broader value (e.g. blanket
        // authUserID check) would reopen the fallback.
        $this->assertMatchesRegularExpression(
            '/AclMain::aclCheckCore\(\s*[\'"]patientportal[\'"]\s*,\s*[\'"]portal[\'"]/',
            $this->machineConfigContent,
            '_machine_config.php must call AclMain::aclCheckCore("patientportal", "portal") on the core-fallback branch — the same ACL enforced by ProviderController::Home()',
        );
    }

    public function testMachineConfigDestroysCoreSessionOnAclDenial(): void
    {
        // If the ACL fails, the code explicitly destroys the core session
        // before bouncing to the landing page. Leaving the session intact
        // would let the caller retry other portal URLs and reuse the
        // fallback session against them.
        $this->assertStringContainsString(
            'destroyCoreSession',
            $this->machineConfigContent,
            'Failed core-fallback ACL check must destroy the core session so the caller cannot reuse it against sibling portal endpoints',
        );
    }

    public function testMachineConfigCoreFallbackBranchExits(): void
    {
        // Sanity check: the fallback branch that runs when ACL fails must
        // terminate the request — otherwise downstream includes would run
        // with `authUserID` set and no bootstrap_pid.
        $this->assertMatchesRegularExpression(
            '/AclMain::aclCheckCore\([^)]+\).*?exit\s*;/s',
            $this->machineConfigContent,
            'Core-fallback ACL failure must exit — do not let downstream code run with a session that failed the ACL check',
        );
    }

    // The router's own rules (the core-user fallback may reach only All and Staff routes) are
    // covered by PortalRouterTest.

    /**
     * Document routes are open to every caller, because the controller checks each document itself.
     */
    public function testOnsiteDocumentRoutesRemainOpenToTheControllerCheck(): void
    {
        foreach (PortalRouteTable::routes() as $route) {
            if ($route->controller === OnsiteDocumentController::class) {
                $this->assertSame(RouteAccess::All, $route->access, $route->key() . ' is checked per document by the controller');
            }
        }
    }
}
