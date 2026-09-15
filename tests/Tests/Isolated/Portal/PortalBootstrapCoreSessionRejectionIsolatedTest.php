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
 *   2. `portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php`
 *      enforces `p_acl` for the core-user fallback in addition to the
 *      patient-portal path. Only `p_all` routes are open to the fallback;
 *      `p_none` / `p_limited` routes deny (they are patient-scoped and
 *      their binding logic assumes `bootstrap_pid`).
 *
 * Both files are legacy include-style scripts with process-terminating
 * side effects (`exit()`, header emission, `interface/globals.php` require)
 * so a real dispatch invocation is out of scope for the isolated tier.
 * Source-file text inspection matches the pattern used elsewhere in this
 * suite (see `FhirRouteAclEnforcementIsolatedTest`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use PHPUnit\Framework\TestCase;

final class PortalBootstrapCoreSessionRejectionIsolatedTest extends TestCase
{
    private string $machineConfigContent = '';
    private string $genericRouterContent = '';

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

        $routerPath = realpath(__DIR__ . '/../../../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php');
        if (!is_string($routerPath)) {
            $this->markTestSkipped('GenericRouter.php not found');
        }
        $router = file_get_contents($routerPath);
        if ($router === false) {
            $this->markTestSkipped('Failed to read GenericRouter.php');
        }
        $this->genericRouterContent = $router;
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
            '_machine_config.php must call AclMain::aclCheckCore("patientportal", "portal") on the core-fallback branch — the same ACL enforced by ProviderHome.tpl.php',
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

    // -------------------------------------------------------------------------
    // GenericRouter — p_acl enforcement for core-user fallback branch
    // -------------------------------------------------------------------------

    public function testGenericRouterHasCoreFallbackAclEnforcementOnLiteralRoutes(): void
    {
        // The literal-match branch: for the core-user fallback (bootstrap_pid
        // empty) any route that isn't `p_all` must deny. Otherwise a core
        // user who happens to hold `patientportal/portal` reaches
        // patient-scoped routes whose controllers assume bootstrap_pid is
        // populated.
        //
        // The regex matches the literal-match branch's else-case: the
        // condition `$pAcl != 'p_all'` sits inside an else block guarded
        // by the same bootstrapPid variable.
        $this->assertMatchesRegularExpression(
            '/empty\(\$bootstrapPid\).*?\$pAcl\s*!=\s*[\'"]p_all[\'"]/s',
            $this->genericRouterContent,
            'GenericRouter literal-match branch must deny non-p_all routes when bootstrap_pid is empty (core-user fallback)',
        );
    }

    public function testGenericRouterHasCoreFallbackAclEnforcementOnWildcardRoutes(): void
    {
        // Same shape for the wildcard-match branch which handles routes
        // like `GET:api/onsitedocument/(:num)`. The uncovered path here
        // touched wildcard routes.
        $this->assertMatchesRegularExpression(
            '/empty\(\$bootstrapPid\).*?\$p_acl\s*!=\s*[\'"]p_all[\'"]/s',
            $this->genericRouterContent,
            'GenericRouter wildcard-match branch must deny non-p_all routes when bootstrap_pid is empty (core-user fallback)',
        );
    }

    public function testGenericRouterHasNoRemainingUnguardedBootstrapPidBranch(): void
    {
        // Regression sentinel: the historical code path was
        //   if (!empty($globalsBag->get('bootstrap_pid'))) { p_acl check }
        // with no else clause. If someone reverts back to that shape, this
        // assertion catches it. We assert the substring literal is gone —
        // both refactored branches now use the local `$bootstrapPid` var.
        $this->assertStringNotContainsString(
            "if (!empty(\$globalsBag->get('bootstrap_pid'))) {\n                // p_acl check",
            $this->genericRouterContent,
            'The original bootstrap_pid-only p_acl guard must not return — the else branch (core-user fallback) needs enforcement too',
        );
    }

    // -------------------------------------------------------------------------
    // Route table shape sentinel — OnsiteDocument routes remain p_all,
    // which means the router-level gate is the ONLY line blocking core-user
    // fallback traffic from reaching them (the controllers are "secured
    // downstream" per the p_all comment, but that downstream check assumes
    // bootstrap_pid is set — which the core-user fallback path did not
    // populate).
    // -------------------------------------------------------------------------

    public function testOnsiteDocumentRoutesRemainMarkedPAll(): void
    {
        $appConfigPath = realpath(__DIR__ . '/../../../../portal/patient/_app_config.php');
        $this->assertIsString($appConfigPath, 'portal/patient/_app_config.php must exist');
        $content = file_get_contents($appConfigPath);
        $this->assertIsString($content, '_app_config.php must be readable');

        // If any of the OnsiteDocument routes drop below p_all the shape
        // is still safe (stricter is safer) — but the intended architecture
        // is downstream-checked + p_all, and this sentinel just ensures
        // the shape hasn't changed such that our other tests are asking
        // the wrong question.
        $this->assertMatchesRegularExpression(
            '/[\'"]GET:api\/onsitedocuments[\'"]\s*=>\s*\[[^]]*\'p_acl\'\s*=>\s*\'p_all\'/s',
            $content,
            'GET:api/onsitedocuments route is expected to remain p_all — this test\'s router assertions assume that shape'
        );
    }
}
