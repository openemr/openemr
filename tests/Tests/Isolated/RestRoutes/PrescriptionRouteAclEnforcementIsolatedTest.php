<?php

/**
 * PrescriptionRouteAclEnforcementIsolatedTest
 *
 * The four prescription REST routes previously all gated on `patients/med`
 * — the Medical Records / History ACL — which let a user with only
 * read-level medical-records access reach the POST and DELETE routes. That
 * combined with the standard REST bridge (APICSRFTOKEN) let a caller with
 * no prescription-write ACL enumerate every prescription tenant-wide,
 * create a new prescription for any patient, and deactivate any
 * prescription by uuid.
 *
 * The ACL check is now `patients/rx` on all four routes plus the
 * appropriate permission bit (`write` on DELETE, `write`/`addonly` on POST)
 * so a caller with only view access to prescriptions cannot hit the write
 * endpoints. This test asserts the invariants textually on
 * `apis/routes/_rest_routes_standard.inc.php` — a source-file inspection
 * mirroring FhirRouteAclEnforcementIsolatedTest so no dispatcher / session
 * / database is required and a future change that reverts one of the gates
 * trips this test before it ships.
 *
 * Design rationale for source-inspection rather than dispatch-invocation
 * tests: the standard-route callbacks reach `RestConfig::request_authorization_check`
 * which reads the process-wide `$_SESSION` / `authUser`. Building a full
 * dispatch harness that authenticates a real user is out of scope for an
 * isolated (no-DB, no-session) test tier — the DB-backed API tests under
 * `tests/Tests/Api/PrescriptionApiTest.php` cover live enforcement. What
 * we want to lock in here is that the route file has not silently drifted:
 * someone reverting one of the ACL gates would fail this test even without
 * shipping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestRoutes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('security')]
class PrescriptionRouteAclEnforcementIsolatedTest extends TestCase
{
    private string $routeContent = '';

    protected function setUp(): void
    {
        $resolved = realpath(__DIR__ . '/../../../../apis/routes/_rest_routes_standard.inc.php');
        if (!is_string($resolved)) {
            $this->markTestSkipped('Standard route file not found');
        }
        $content = file_get_contents($resolved);
        if ($content === false) {
            $this->markTestSkipped('Failed to read standard route file');
        }
        $this->routeContent = $content;
    }

    /**
     * Extract the closure body for a single route entry, keyed by its
     * "METHOD /api/…" literal. Returns the substring between the opening
     * `function (…) {` and the matching closing brace of the closure at
     * outer indentation. Returns '' if the route was not found — the
     * assertions on the caller side will fail loudly rather than accept
     * an empty body as passing.
     */
    private function extractRouteBody(string $routeKey): string
    {
        $escapedKey = preg_quote($routeKey, '/');
        $pattern = '/["\']' . $escapedKey . '["\']\s*=>\s*function\s*\([^)]*\)\s*\{(.*?)\n    \},/s';
        if (preg_match($pattern, $this->routeContent, $matches) === 1) {
            return $matches[1];
        }
        return '';
    }

    // -------------------------------------------------------------------------
    // All four routes must gate on patients/rx, not patients/med
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function prescriptionRouteProvider(): array
    {
        return [
            'list route' => ['GET /api/prescription'],
            'single route' => ['GET /api/prescription/:uuid'],
            'create route' => ['POST /api/prescription'],
            'delete route' => ['DELETE /api/prescription/:uuid'],
        ];
    }

    #[DataProvider('prescriptionRouteProvider')]
    public function testRouteGatesOnPatientsRxNotPatientsMed(string $routeKey): void
    {
        $body = $this->extractRouteBody($routeKey);
        $this->assertNotSame('', $body, sprintf('%s route must exist', $routeKey));
        $this->assertMatchesRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']patients["\'],\s*["\']rx["\']/',
            $body,
            sprintf(
                '%s must gate on (patients, rx) — was previously (patients, med) which let read-level medical-records users through',
                $routeKey
            )
        );
        // Belt-and-braces: make sure a stray patients/med gate did not survive
        // (e.g. a copy-paste reintroducing the Medical Records ACL).
        $this->assertDoesNotMatchRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']patients["\'],\s*["\']med["\']/',
            $body,
            sprintf('%s must NOT reference the previous (patients, med) ACL', $routeKey)
        );
    }

    // -------------------------------------------------------------------------
    // Write routes must additionally require the write permission bit
    // -------------------------------------------------------------------------

    public function testPostRouteRequiresWriteOrAddonlyPermissionBit(): void
    {
        $body = $this->extractRouteBody('POST /api/prescription');
        $this->assertNotSame('', $body, 'POST /api/prescription route must exist');
        // The AclMain::aclCheckCore fourth argument is an array of accepted
        // return values. For a POST/create endpoint either `write` or
        // `addonly` is acceptable (Clinicians ship with `addonly`; Physicians
        // and Administrators ship with `write` — see acl_upgrade.php:379-534).
        // The previous call passed no permission bit at all, so any
        // allow-row on `patients/rx` counted, including plain view.
        $this->assertMatchesRegularExpression(
            "/RestConfig::request_authorization_check\(\s*\\\$request,\s*['\"]patients['\"],\s*['\"]rx['\"],\s*\[\s*['\"]write['\"]\s*,\s*['\"]addonly['\"]\s*\]/",
            $body,
            'POST /api/prescription must require the write OR addonly permission bit — plain patients/rx view must not be sufficient to create a prescription'
        );
    }

    public function testDeleteRouteRequiresWritePermissionBit(): void
    {
        $body = $this->extractRouteBody('DELETE /api/prescription/:uuid');
        $this->assertNotSame('', $body, 'DELETE /api/prescription/:uuid route must exist');
        $this->assertMatchesRegularExpression(
            "/RestConfig::request_authorization_check\(\s*\\\$request,\s*['\"]patients['\"],\s*['\"]rx['\"],\s*\[\s*['\"]write['\"]\s*\]/",
            $body,
            'DELETE /api/prescription/:uuid must require the write permission bit — plain patients/rx view must not be sufficient to deactivate a prescription'
        );
    }

    // -------------------------------------------------------------------------
    // The ACL check must run inline in each route closure so the APICSRFTOKEN
    // local-session bridge cannot bypass it (mirrors the meta-assertion in
    // FhirRouteAclEnforcementIsolatedTest).
    // -------------------------------------------------------------------------

    #[DataProvider('prescriptionRouteProvider')]
    public function testEachRouteCallsInlineAclCheck(string $routeKey): void
    {
        $body = $this->extractRouteBody($routeKey);
        $this->assertNotSame('', $body, sprintf('%s route must exist', $routeKey));
        $this->assertStringContainsString(
            'RestConfig::request_authorization_check(',
            $body,
            sprintf(
                '%s must call RestConfig::request_authorization_check inline — do not move to an event listener that respects skipAuthorization, which lets the local-session bridge bypass the check for this route',
                $routeKey
            )
        );
    }
}
