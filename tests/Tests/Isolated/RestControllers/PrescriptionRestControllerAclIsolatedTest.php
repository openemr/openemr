<?php

/**
 * PrescriptionRestControllerAclIsolatedTest
 *
 * Textual invariants for the per-patient ACL gate on the staff REST path
 * (/api/prescription). The gate lives on the controller so the FHIR/SMART
 * path — which delegates to PrescriptionService via
 * FhirMedicationRequestService — is unaffected (its per-patient
 * authorization runs earlier in BearerTokenAuthorizationStrategy).
 *
 * Locks in the shape rather than the runtime: standing up an authorized
 * session + real AclMain lookups is out of scope for isolated tier
 * (covered by DB-backed API tests in tests/Tests/Api).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrescriptionRestControllerAclIsolatedTest extends TestCase
{
    private string $controllerContent = '';

    protected function setUp(): void
    {
        $resolved = realpath(__DIR__ . '/../../../../src/RestControllers/PrescriptionRestController.php');
        if (!is_string($resolved)) {
            $this->markTestSkipped('PrescriptionRestController.php not found');
        }
        $content = file_get_contents($resolved);
        if ($content === false) {
            $this->markTestSkipped('Failed to read PrescriptionRestController.php');
        }
        $this->controllerContent = $content;
    }

    public function testControllerImportsAclMain(): void
    {
        $this->assertMatchesRegularExpression(
            '/use\s+OpenEMR\\\\Common\\\\Acl\\\\AclMain;/',
            $this->controllerContent,
            'PrescriptionRestController must import AclMain — the per-patient gate lives here so the FHIR/SMART path stays unaffected'
        );
    }

    public function testDenyHelperCallsPatientsDemo(): void
    {
        // The controller helper must run the `patients / demo` check,
        // mirroring the UI's demographics guard.
        $this->assertMatchesRegularExpression(
            "/AclMain::aclCheckCore\\(\\s*['\"]patients['\"]\\s*,\\s*['\"]demo['\"]\\s*\\)/",
            $this->controllerContent,
            'denyIfNoChartAccess must call AclMain::aclCheckCore(patients, demo)'
        );
    }

    public function testDenyHelperCallsSquadCheck(): void
    {
        // When the patient carries a squad tag, additionally gate on
        // `squads / <squad>`. Mirrors BearerTokenAuthorizationStrategy.
        $this->assertMatchesRegularExpression(
            "/AclMain::aclCheckCore\\(\\s*['\"]squads['\"]\\s*,\\s*\\\$squad\\s*\\)/",
            $this->controllerContent,
            'denyIfNoChartAccess must gate on squads/<squad> when the patient carries a squad tag'
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function gatedMethodProvider(): array
    {
        return [
            'getAll'  => ['public function getAll'],
            'getOne'  => ['public function getOne'],
            'delete'  => ['public function delete'],
        ];
    }

    #[DataProvider('gatedMethodProvider')]
    public function testGatedMethodInvokesDenyHelper(string $methodSignature): void
    {
        // Each read/delete method must call the gate before delegating to
        // the service. Match: methodSignature, then a call to
        // denyIfNoChartAccess, then an early return on non-null result.
        $pattern = '/' . preg_quote($methodSignature, '/') . '[\s\S]{0,1500}?\$denied\s*=\s*\$this->denyIfNoChartAccess\([\s\S]{0,200}?if\s*\(\s*\$denied\s*!==\s*null\s*\)\s*\{\s*return\s+\$denied;/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->controllerContent,
            sprintf('%s must call denyIfNoChartAccess and return early on denial', $methodSignature)
        );
    }

    public function testGetAllResolvesPatientByUuidBeforeGate(): void
    {
        // getAll receives the patient uuid via query param. It must resolve
        // the row (for the squad tag) via findPatientByPatientUuid before
        // handing to the gate.
        $this->assertMatchesRegularExpression(
            '/public function getAll[\s\S]{0,1500}?\$this->prescriptionService->findPatientByPatientUuid\(/',
            $this->controllerContent,
            'getAll must resolve the target patient row via findPatientByPatientUuid before the ACL check'
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ownerResolvingMethodProvider(): array
    {
        return [
            'getOne' => ['public function getOne'],
            'delete' => ['public function delete'],
        ];
    }

    #[DataProvider('ownerResolvingMethodProvider')]
    public function testOwnerResolvingMethodUsesFindPatientForPrescription(string $methodSignature): void
    {
        // getOne/delete receive a prescription uuid, not a patient uuid.
        // The controller must resolve the owner via
        // findPatientForPrescription so the gate can check chart access
        // against the actual owner.
        $pattern = '/' . preg_quote($methodSignature, '/') . '[\s\S]{0,600}?\$this->prescriptionService->findPatientForPrescription\(/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->controllerContent,
            sprintf('%s must resolve the prescription owner via findPatientForPrescription before the ACL check', $methodSignature)
        );
    }

    public function testDeleteAclRunsIndependentOfExpectedPatientUuid(): void
    {
        // Rabbit-flagged: the optional query hint (`patient_uuid`) must NOT
        // gate the ACL check. Lock ordering: findPatientForPrescription +
        // denyIfNoChartAccess appear BEFORE the query-hint read.
        $body = $this->extractMethodBody('public function delete');
        $this->assertNotSame('', $body, 'delete() method must exist');

        $findOffset = strpos($body, 'findPatientForPrescription(');
        $denyOffset = strpos($body, 'denyIfNoChartAccess(');
        $hintOffset = strpos($body, "getString('patient_uuid')");

        $this->assertIsInt($findOffset, 'delete must resolve the owner');
        $this->assertIsInt($denyOffset, 'delete must invoke the deny helper');
        $this->assertIsInt($hintOffset, 'delete must still read the optional patient_uuid query hint');

        $this->assertLessThan($hintOffset, $findOffset, 'owner lookup must precede the query-hint read');
        $this->assertLessThan($hintOffset, $denyOffset, 'ACL check must precede the query-hint read');
    }

    /**
     * Extract the body of a named method by matching its signature and
     * capturing until the matching closing brace. Returns '' when the
     * method cannot be located.
     */
    private function extractMethodBody(string $signature): string
    {
        $escaped = preg_quote($signature, '/');
        $pattern = '/' . $escaped . '\s*\([^)]*\)[^\{]*\{(.*?)\n    \}/s';
        if (preg_match($pattern, $this->controllerContent, $matches) === 1) {
            return $matches[1];
        }
        return '';
    }
}
