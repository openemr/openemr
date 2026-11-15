<?php

/**
 * API-tier coverage for OpenEMR's Bulk Data IG endpoints beyond the
 * one-method `GroupExportFhirApiTest`:
 *
 *   - GET  /fhir/Patient/$export        — patient-group export
 *   - GET  /fhir/$export                — system-level export
 *   - GET  /fhir/$bulkdata-status       — job status poll
 *   - DELETE /fhir/$bulkdata-status     — job cancel
 *
 * The sibling test (`GroupExportFhirApiTest::testGroupExportWithNonExistingGroupId`)
 * exercises only Group/$export. The three remaining export entrypoints plus
 * the status + cancel paths had no api-tier coverage before this file.
 *
 * Covered here:
 *   - Happy start of Patient/$export (asserts async 202 + Content-Location shape)
 *   - Happy start of system /$export (asserts async 202 + Content-Location shape)
 *   - Accept-header gate (missing header or wrong value → 400 OperationOutcome)
 *   - Prefer-header gate (missing or non-`respond-async` → 400 OperationOutcome)
 *   - Status with nonexistent job uuid → 404 OperationOutcome
 *   - DELETE with nonexistent job uuid → 404 OperationOutcome
 *   - DELETE of an active job → 202 Accepted (cancel round-trip)
 *   - Status with NO `job` query param → currently 500 (markTestSkipped pending the
 *     `TODO: @adunsulag need to fix a bulkdata-status test where there is no job id.
 *     Should not throw a 500 error.` in GroupExportFhirApiTest). Skipping + comment
 *     documents current behavior so the flip-to-pass is visible when the fix lands.
 *
 * All tests use `BulkAPITestClient` (system-scope JWT client-credentials) and align
 * `site_addr_oath` with the test base URL at setUp, following the GroupExportFhirApiTest
 * precedent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class BulkFhirOperationsApiTest extends TestCase
{
    private BulkAPITestClient $testClient;
    private ?string $savedSiteAddrOath = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new BulkAPITestClient($baseUrl, false);
        // V2 scopes include system/Patient.$export + system/*.$export which
        // the kick-off tests below exercise. The default SYSTEM_SCOPES
        // (V1) covers only Group.$export and would 401 the Patient/$export
        // and /$export routes on scope-mismatch, before the Accept /
        // Prefer header gates fire.
        $this->testClient->setScopesForBulkData(BulkAPITestClient::SYSTEM_SCOPES_V2);

        // Same alignment GroupExportFhirApiTest uses. Without this the JWT
        // client-assertion's aud claim mismatches the server's expected
        // audience in dev-compose-shaped envs, and the token request's 400
        // causes the bearer to be empty + the FHIR request to 401.
        $row = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'site_addr_oath'");
        $value = is_array($row) ? ($row['gl_value'] ?? null) : null;
        $this->savedSiteAddrOath = is_string($value) ? $value : null;
        QueryUtils::sqlStatementThrowException(
            "UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'",
            [$baseUrl]
        );
    }

    public function tearDown(): void
    {
        try {
            $this->testClient->cleanupRevokeAuth();
            $this->testClient->cleanupClient();
        } finally {
            if ($this->savedSiteAddrOath !== null) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'",
                    [$this->savedSiteAddrOath]
                );
                $this->savedSiteAddrOath = null;
            }
        }
    }

    /**
     * Bulk Data IG § Kick-off requires a 202 Accepted response with a
     * Content-Location header pointing at the status URL. OpenEMR serves
     * the status endpoint at `/fhir/$bulkdata-status?job=<uuid>`.
     */
    public function testPatientExportStartsAsyncJobAndIssuesStatusUrl(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->setBulkExportHeaders();

        $response = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(
            Response::HTTP_ACCEPTED,
            $response->getStatusCode(),
            'Patient/$export must respond 202 Accepted per Bulk Data IG kick-off'
        );
        $this->assertContentLocationShape($response);
    }

    /**
     * System-level /$export is distinct from Patient/$export — the former
     * is a server-wide export, the latter is scoped to Patient compartment.
     */
    public function testSystemExportStartsAsyncJobAndIssuesStatusUrl(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->setBulkExportHeaders();

        $response = $this->testClient->get('/apis/default/fhir/$export');
        $this->assertSame(
            Response::HTTP_ACCEPTED,
            $response->getStatusCode(),
            'System /$export must respond 202 Accepted per Bulk Data IG kick-off'
        );
        $this->assertContentLocationShape($response);
    }

    /**
     * Bulk Data IG requires Accept: application/fhir+json. A missing or
     * wrong Accept must 400 with an OperationOutcome body before any job
     * work begins. `FhirOperationExportRestController::validateHeaders`
     * throws `InvalidExportHeaderException` which the controller catches
     * and renders as 400.
     */
    public function testPatientExportRejectsWithoutAcceptHeader(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        // Deliberately omit the Accept header; keep Prefer so the failure
        // is isolated to Accept.
        $this->testClient->setHeaders([
            'Prefer' => FhirOperationExportRestController::PREFER_HEADER,
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'Patient/$export without Accept must 400'
        );
        $this->assertOperationOutcomeBody($response);
    }

    public function testPatientExportRejectsWithWrongAcceptHeader(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->testClient->setHeaders([
            // application/json is not application/fhir+json.
            'Accept' => 'application/json',
            'Prefer' => FhirOperationExportRestController::PREFER_HEADER,
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertOperationOutcomeBody($response);
    }

    /**
     * Bulk Data IG requires Prefer: respond-async. A missing or wrong
     * Prefer must 400.
     */
    public function testPatientExportRejectsWithoutPreferHeader(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->testClient->setHeaders([
            'Accept' => FhirOperationExportRestController::ACCEPT_HEADER_OPERATION_OUTCOME,
            // Deliberately omit Prefer.
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertOperationOutcomeBody($response);
    }

    public function testPatientExportRejectsWithWrongPreferValue(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->testClient->setHeaders([
            'Accept' => FhirOperationExportRestController::ACCEPT_HEADER_OPERATION_OUTCOME,
            // respond-sync is not respond-async.
            'Prefer' => 'respond-sync',
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertOperationOutcomeBody($response);
    }

    /**
     * A GET /$bulkdata-status against a bogus uuid must 404 (not 200 with
     * empty body and not 500). `processExportStatusRequestForJob` catches
     * `InvalidArgumentException` from the service layer and renders a
     * 404 OperationOutcome.
     */
    public function testStatusWithNonexistentJobReturns404(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->testClient->setHeaders([
            'Accept' => 'application/fhir+json',
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->get(
            '/apis/default/fhir/$bulkdata-status',
            ['job' => '99999999-511f-4f6d-bc97-b65a78cf1996']
        );
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertOperationOutcomeBody($response);
    }

    /**
     * DELETE /$bulkdata-status?job=<bogus-uuid> must 404, not 500.
     * `processDeleteExportForJob` catches the service-layer
     * InvalidArgumentException and renders a 404 OperationOutcome.
     */
    public function testDeleteWithNonexistentJobReturns404(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->testClient->setHeaders([
            'Accept' => 'application/fhir+json',
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $response = $this->testClient->deleteWithQuery(
            '/apis/default/fhir/$bulkdata-status',
            ['job' => '99999999-511f-4f6d-bc97-b65a78cf1996']
        );
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertOperationOutcomeBody($response);
    }

    /**
     * Start an export, then DELETE its job uuid. The spec allows the server
     * to cancel an in-progress export; OpenEMR's `processDeleteExportForJob`
     * returns 202 Accepted on successful delete. This locks the full
     * start → cancel round-trip from the external-client angle.
     */
    public function testDeleteOfActiveJobReturnsAccepted(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->setBulkExportHeaders();

        // Kick off a Patient export to get a real job uuid.
        $kickoff = $this->testClient->get('/apis/default/fhir/Patient/$export');
        $this->assertSame(Response::HTTP_ACCEPTED, $kickoff->getStatusCode());
        $contentLocation = $kickoff->getHeaders()['Content-Location'][0] ?? null;
        $this->assertNotEmpty($contentLocation, 'Content-Location should be present on kick-off');

        $query = [];
        parse_str((string) parse_url((string) $contentLocation, PHP_URL_QUERY), $query);
        $job = $query['job'] ?? null;
        $this->assertIsString($job);
        $this->assertNotSame('', $job);

        // DELETE the job. Reset headers to a plain Accept — the DELETE
        // doesn't need the respond-async Prefer that kick-off requires.
        $this->testClient->setHeaders([
            'Accept' => 'application/fhir+json',
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
        $delete = $this->testClient->deleteWithQuery(
            '/apis/default/fhir/$bulkdata-status',
            ['job' => $job]
        );
        $this->assertSame(
            Response::HTTP_ACCEPTED,
            $delete->getStatusCode(),
            'DELETE on an existing export job should return 202 Accepted'
        );
    }

    /**
     * Status with no `job` query param — currently returns 500 per the
     * TODO in GroupExportFhirApiTest ("Should not throw a 500 error").
     * Skip for now so the TODO stays visible and the flip-to-pass surfaces
     * when the implementation lands. Same markTestSkipped pattern the FHIR
     * search-modifier matrix uses for known-latent bugs.
     */
    public function testStatusWithoutJobQueryParamReturnsOperationOutcomeNot500(): void
    {
        $this->markTestSkipped(
            'Known behavior: /$bulkdata-status without job=<uuid> currently throws 500. '
            . 'Should return 400 or 404 OperationOutcome. Tracked in GroupExportFhirApiTest '
            . 'as "TODO: need to fix a bulkdata-status test where there is no job id". '
            . 'Flip this to assert the actual 400/404 + OperationOutcome shape when the '
            . 'controller is fixed to distinguish missing-param from invalid-uuid.'
        );
    }

    /**
     * Shared Accept + Prefer + Bearer header setup for kick-off requests.
     */
    private function setBulkExportHeaders(): void
    {
        $this->testClient->setHeaders([
            'Accept' => FhirOperationExportRestController::ACCEPT_HEADER_OPERATION_OUTCOME,
            'Prefer' => FhirOperationExportRestController::PREFER_HEADER,
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken(),
        ]);
    }

    /**
     * Content-Location on a kick-off response must point at the status
     * endpoint with a `job` query parameter. Shape pinned so a regression
     * in the status-URL format fails loudly rather than masquerading.
     */
    private function assertContentLocationShape(\Psr\Http\Message\ResponseInterface $response): void
    {
        $contentLocation = $response->getHeaders()['Content-Location'][0] ?? null;
        $this->assertNotEmpty(
            $contentLocation,
            'Content-Location header must be present on kick-off per Bulk Data IG'
        );
        $this->assertStringContainsString(
            '/fhir/$bulkdata-status',
            (string) $contentLocation,
            'Content-Location should point at /$bulkdata-status'
        );
        $query = [];
        parse_str((string) parse_url((string) $contentLocation, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey(
            'job',
            $query,
            'Content-Location must carry a job uuid as a query parameter'
        );
        $this->assertIsString($query['job']);
        $this->assertNotSame('', $query['job']);
    }

    /**
     * Error responses on export endpoints are FHIR OperationOutcome
     * resources per the Bulk Data IG. Pin the resourceType so a
     * regression that returned a raw JSON error object (or empty body)
     * fails loudly.
     */
    private function assertOperationOutcomeBody(\Psr\Http\Message\ResponseInterface $response): void
    {
        $raw = (string) $response->getBody();
        $decoded = json_decode($raw, true);
        $this->assertIsArray($decoded, "Response body must be JSON; got: " . substr($raw, 0, 500));
        $this->assertSame(
            'OperationOutcome',
            $decoded['resourceType'] ?? null,
            'Error response must be a FHIR OperationOutcome resource'
        );
    }
}
