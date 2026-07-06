<?php

/**
 * FHIR Condition API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vasilii Tereshchenko <vasilii.tereshchenko@gmail.com>
 * @copyright Copyright (c) 2026 Vasilii Tereshchenko <vasilii.tereshchenko@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Condition endpoints:
 *   GET /fhir/Condition          (search -> returns a Bundle)
 *   GET /fhir/Condition/:uuid     (read one -> returns a single Condition)
 *
 * Modeled on PatientFhirApiTest, but with one big difference: there is NO
 * POST /fhir/Condition route, so we cannot create test data by POSTing a
 * Condition the way the Patient test POSTs a Patient. Instead we seed a
 * patient + a medical-problem row directly through FixtureManager in setUp,
 * and remove it in tearDown via ConditionFixtureManager::removeFixtures().
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService), not FHIR's "searchset".
 * - getOne errors return validationErrors (400) or an empty JSON array (404), not OperationOutcome.
 *   A follow-up issue could align these with FHIR if desired.
 * - Problem-list Conditions expose code.text from the list title when diagnosis is not parsed
 *   into structured coding (see FhirConditionProblemListItemService::searchForOpenEMRRecords).
 *   ICD-10 coding (code.coding) requires a separate product fix; do not assert it here.
 */
class ConditionFhirApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;

    /** FHIR uuid of the condition we seed, so we can read it back and assert on it. */
    private string $conditionUuid;

    /** FHIR uuid of the patient that owns the seeded condition. */
    private string $patientUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $seeded = $this->fixtureManager->installConditionFixtures();
        $this->patientUuid = $seeded['patientUuid'];
        $this->conditionUuid = $seeded['conditionUuid'];
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeConditionFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Happy path: search
    // ---------------------------------------------------------------------

    /**
     * Searching by patient should return a FHIR Bundle containing the seeded condition.
     *
     * OpenEMR returns bundle type "collection", not FHIR's conventional "searchset".
     */
    public function testSearchReturnsCollectionBundle(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Condition",
            ['patient' => $this->patientUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $body = $result->getBody()->getContents();
        $this->assertNotEmpty($body, "Condition search should return a body");
        $bundle = json_decode((string) $body, true);
        $this->assertIsArray($bundle);

        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertEquals("collection", $bundle['type'] ?? null);
        $this->assertArrayHasKey("entry", $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertNotEmpty($bundle['entry'], "Search should return at least the seeded condition");

        $foundSeededCondition = false;
        foreach ($bundle['entry'] as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey("resource", $entry);
            $this->assertIsArray($entry['resource']);
            $this->assertEquals("Condition", $entry['resource']['resourceType'] ?? null);
            if (($entry['resource']['id'] ?? null) === $this->conditionUuid) {
                $foundSeededCondition = true;
            }
        }
        $this->assertTrue($foundSeededCondition, "Search should include the seeded condition");
    }

    /**
     * Searching by the seeded condition's _id should return exactly that one
     * condition, and it should reference the seeded patient as its subject.
     */
    public function testSearchByIdReturnsSeededCondition(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Condition",
            ['_id' => $this->conditionUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertArrayHasKey("entry", $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertCount(1, $bundle['entry'], "Search by _id should return a single condition");

        $this->assertIsArray($bundle['entry'][0]);
        $this->assertArrayHasKey('resource', $bundle['entry'][0]);
        $resource = $bundle['entry'][0]['resource'];
        $this->assertIsArray($resource);
        $this->assertEquals($this->conditionUuid, $resource['id']);

        // The subject reference should point at our patient, e.g. "Patient/<uuid>".
        $this->assertArrayHasKey("subject", $resource);
        $this->assertIsArray($resource['subject']);
        $this->assertArrayHasKey('reference', $resource['subject']);
        $this->assertIsString($resource['subject']['reference']);
        $this->assertStringContainsString(
            $this->patientUuid,
            $resource['subject']['reference'],
            "Condition subject should reference the seeded patient"
        );
        /** @var array<string, mixed> $resource */
        $this->assertConditionCodeText($resource);
    }

    // ---------------------------------------------------------------------
    // Happy path: read one
    // ---------------------------------------------------------------------

    /**
     * Reading a single condition by uuid should return a Condition resource
     * with the US Core required elements populated.
     */
    public function testGetOneReturnsConditionResource(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Condition/" . $this->conditionUuid
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $resource = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($resource);
        $this->assertEquals("Condition", $resource['resourceType'] ?? null);
        $this->assertEquals($this->conditionUuid, $resource['id'] ?? null);

        // US Core Condition required-ish elements. If the seed data omits any of
        // these, tighten the fixture rather than loosening the assert.
        $this->assertArrayHasKey("clinicalStatus", $resource);
        $this->assertArrayHasKey("category", $resource);
        $this->assertArrayHasKey("code", $resource);
        $this->assertArrayHasKey("subject", $resource);
        $this->assertIsArray($resource['subject']);
        $this->assertArrayHasKey('reference', $resource['subject']);
        $this->assertIsString($resource['subject']['reference']);
        $this->assertStringContainsString(
            $this->patientUuid,
            $resource['subject']['reference']
        );
        /** @var array<string, mixed> $resource */
        $this->assertConditionCodeText($resource);
    }

    // ---------------------------------------------------------------------
    // Empty result (this is the one people get wrong: it's 200, not 404)
    // ---------------------------------------------------------------------

    /**
     * A search that matches nothing should return an empty Bundle
     * with HTTP 200 -- NOT a 404.
     */
    public function testSearchWithNoMatchReturnsEmptyBundle(): void
    {
        // A well-formed uuid that will not exist in the DB.
        $randomUuid = "00000000-0000-0000-0000-000000000000";

        $result = $this->testClient->get(
            "/apis/default/fhir/Condition",
            ['_id' => $randomUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertEquals("collection", $bundle['type'] ?? null);
        $this->assertEquals(0, $bundle['total'] ?? null, "No-match search should have total 0");
        $this->assertEmpty($bundle['entry'] ?? [], "No-match search should have no entries");
    }

    // ---------------------------------------------------------------------
    // Error paths: read one
    // ---------------------------------------------------------------------

    /**
     * A malformed uuid should produce HTTP 400 with validationErrors (not OperationOutcome).
     */
    public function testGetOneMalformedUuidReturnsBadRequest(): void
    {
        $result = $this->testClient->get("/apis/default/fhir/Condition/not-a-uuid");

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("validationErrors", $body);
    }

    /**
     * A well-formed but non-existent uuid should return 404 with an empty JSON array (not OperationOutcome).
     */
    public function testGetOneNonexistentUuidReturnsNotFound(): void
    {
        $absentUuid = "11111111-1111-1111-1111-111111111111";

        $result = $this->testClient->get("/apis/default/fhir/Condition/" . $absentUuid);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $body = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertEmpty($body);
    }

    // ---------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------

    /**
     * Search without a valid token should be rejected with 401 and an OAuth denial body.
     */
    public function testSearchUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(
            getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost",
            false
        );
        // Deliberately do NOT call setAuthToken().
        $result = $unauthClient->get("/apis/default/fhir/Condition");
        $this->assertOAuthUnauthorizedResponse($result);
    }

    /**
     * Read-one without a valid token should be rejected with 401 and an OAuth denial body.
     */
    public function testGetOneUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(
            getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost",
            false
        );
        $result = $unauthClient->get("/apis/default/fhir/Condition/" . $this->conditionUuid);
        $this->assertOAuthUnauthorizedResponse($result);
    }

    private function assertOAuthUnauthorizedResponse(ResponseInterface $response): void
    {
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $body = json_decode((string) $response->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertArrayHasKey('message', $body);
        $this->assertIsString($body['message']);
        $this->assertStringContainsString(
            'denied the request',
            $body['message'],
            '401 should be an OAuth authorization denial, not a routing or server error'
        );
        $this->assertArrayNotHasKey('resourceType', $body, '401 body should not be a FHIR resource');
    }

    /**
     * Assert code on master: problem-list items without parsed diagnosis use title as code.text.
     *
     * @param array<string, mixed> $resource
     */
    private function assertConditionCodeText(array $resource): void
    {
        $this->assertArrayHasKey('code', $resource);
        $this->assertIsArray($resource['code']);
        $this->assertEquals('Essential hypertension', $resource['code']['text'] ?? null);
    }
}
