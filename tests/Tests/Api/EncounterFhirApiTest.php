<?php

/**
 * FHIR Encounter API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @copyright Copyright (c) 2026 Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Encounter endpoints:
 *   GET /fhir/Encounter           (search  -> returns a Bundle)
 *   GET /fhir/Encounter/:uuid      (read one -> returns a single Encounter)
 *
 * Modeled on ConditionFhirApiTest. There is no POST /fhir/Encounter route, so
 * test data is seeded through EncounterFixtureManager in setUp (which also
 * installs the owning patient + facility) and removed in tearDown.
 *
 * These tests PIN CURRENT SERVER BEHAVIOR, not the FHIR spec:
 * - Encounter search bundles use type "collection" (FhirEncounterRestController),
 *   not FHIR's conventional "searchset".
 * - getOne on a well-formed but nonexistent uuid returns 404 with an empty JSON
 *   array; a malformed uuid returns 400 with validationErrors -- not an
 *   OperationOutcome (RestControllerHelper::handleFhirProcessingResult, same
 *   path ConditionFhirApiTest documents).
 */
class EncounterFhirApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private EncounterFixtureManager $fixtureManager;

    /** FHIR uuid of the seeded encounter. */
    private string $encounterUuid;

    /** FHIR uuid of the patient that owns the seeded encounter. */
    private string $patientUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new EncounterFixtureManager();
        $this->fixtureManager->installFixtures();

        // The fixture seeds one encounter whose reason is prefixed 'test-fixture-'
        // (see EncounterFixtureManager::removeInstalledFixtures + encounters.php).
        // Resolve its FHIR uuid and the owning patient's FHIR uuid the same way
        // MedicationDispenseFixtureManager::getEncounterUuid does.
        $enc = QueryUtils::querySingleRow(
            "SELECT uuid, pid FROM form_encounter WHERE reason LIKE 'test-fixture-%' ORDER BY id DESC LIMIT 1"
        );
        $this->assertNotEmpty($enc, "Encounter fixture should have been installed");
        $this->encounterUuid = UuidRegistry::uuidToString($enc['uuid']);

        $pat = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pid = ?",
            [$enc['pid']]
        );
        $this->assertNotEmpty($pat, "Owning patient for encounter fixture should exist");
        $this->patientUuid = UuidRegistry::uuidToString($pat['uuid']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Happy path: search
    // ---------------------------------------------------------------------

    /**
     * Searching by patient should return a FHIR Bundle (type "collection")
     * that includes the seeded encounter.
     */
    public function testSearchByPatientReturnsSeededEncounter(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Encounter",
            ['patient' => $this->patientUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $body = $result->getBody()->getContents();
        $this->assertNotEmpty($body, "Encounter search should return a body");
        $bundle = json_decode((string) $body, true);
        $this->assertIsArray($bundle);
        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertEquals("collection", $bundle['type'] ?? null);
        $this->assertArrayHasKey("entry", $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertNotEmpty($bundle['entry'], "Search should return at least the seeded encounter");

        $found = false;
        foreach ($bundle['entry'] as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey("resource", $entry);
            $this->assertIsArray($entry['resource']);
            $this->assertEquals("Encounter", $entry['resource']['resourceType'] ?? null);
            if (($entry['resource']['id'] ?? null) === $this->encounterUuid) {
                $found = true;
            }
        }
        $this->assertTrue($found, "Search by patient should include the seeded encounter");
    }

    /**
     * Searching by the seeded encounter's _id should return exactly that one
     * encounter, and it should reference the seeded patient as its subject.
     */
    public function testSearchByIdReturnsSeededEncounter(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Encounter",
            ['_id' => $this->encounterUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertArrayHasKey("entry", $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertCount(1, $bundle['entry'], "Search by _id should return a single encounter");

        $this->assertIsArray($bundle['entry'][0]);
        $this->assertArrayHasKey('resource', $bundle['entry'][0]);
        $resource = $bundle['entry'][0]['resource'];
        $this->assertIsArray($resource);
        $this->assertEquals($this->encounterUuid, $resource['id']);
        $this->assertCanonicalPatientSubject($resource);
    }

    /**
     * A search that matches no encounter should return an empty Bundle with
     * HTTP 200 -- NOT a 404.
     */
    public function testSearchNoMatchReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Encounter",
            ['_id' => "00000000-0000-0000-0000-000000000000"]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertEquals("collection", $bundle['type'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null, "No-match search should have total 0");
        $this->assertEmpty($bundle['entry'] ?? [], "No-match search should have no entries");
    }

    // ---------------------------------------------------------------------
    // Happy path: read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded encounter by uuid should return exactly that Encounter,
     * and it should reference the seeded patient as its subject.
     */
    public function testReadByUuidReturnsSeededEncounter(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Encounter/" . $this->encounterUuid
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $resource = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($resource);
        $this->assertEquals("Encounter", $resource['resourceType'] ?? null);
        $this->assertEquals($this->encounterUuid, $resource['id'] ?? null);
        $this->assertCanonicalPatientSubject($resource);
    }

    // ---------------------------------------------------------------------
    // Error paths: read one
    // ---------------------------------------------------------------------

    /**
     * A well-formed but nonexistent uuid should return 404 with an empty JSON
     * array, not a 200, not {}, and not an OperationOutcome.
     */
    public function testReadNonexistentUuidReturnsNotFound(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Encounter/" . $this->fixtureManager->getUnregisteredUuid()
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $raw = trim((string) $result->getBody()->getContents());
        $this->assertNotSame('', $raw, "404 should return a JSON body");
        $this->assertSame(
            '[',
            $raw[0],
            "404 body must be a JSON array [], not an object {} or an OperationOutcome"
        );
        $body = json_decode($raw, true);
        $this->assertIsArray($body);
        $this->assertSame([], $body);
    }

    /**
     * A malformed uuid should return 400 with validationErrors, not an
     * OperationOutcome.
     */
    public function testReadMalformedUuidIsBadRequest(): void
    {
        $result = $this->testClient->get("/apis/default/fhir/Encounter/not-a-valid-uuid");
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("validationErrors", $body);
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
        $result = $unauthClient->get("/apis/default/fhir/Encounter");
        $this->assertOAuthUnauthorizedResponse($result);
    }

    /**
     * Read-one without a valid token should be rejected with 401 and an OAuth denial body.
     */
    public function testReadUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(
            getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost",
            false
        );
        $result = $unauthClient->get("/apis/default/fhir/Encounter/" . $this->encounterUuid);
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
     * Subject must be the canonical FHIR R4 shape "Patient/<uuid>".
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertCanonicalPatientSubject(array $resource): void
    {
        $this->assertArrayHasKey("subject", $resource);
        $this->assertIsArray($resource['subject']);
        $this->assertArrayHasKey('reference', $resource['subject']);
        $this->assertSame(
            'Patient/' . $this->patientUuid,
            $resource['subject']['reference'],
            "Encounter subject must be the canonical FHIR reference Patient/{uuid}"
        );
    }
}
