<?php

/**
 * FHIR MedicationDispense API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\MedicationDispenseFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR MedicationDispense endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/MedicationDispense        (search -> returns a Bundle)
 *   GET /fhir/MedicationDispense/:uuid  (read one -> returns a single MedicationDispense)
 *
 * There is no POST/PUT/DELETE route: dispenses are read-only, derived from the `drug_sales`
 * table via FhirMedicationDispenseLocalDispensaryService. Each test seeds its own drug_sales
 * row (plus the patient, drug, inventory, prescription and encounter it needs) through
 * MedicationDispenseFixtureManager::createDrugSaleDispense(); tearDown removes everything the
 * fixture manager created.
 *
 * The routes are behind `user/MedicationDispense.read`, which ApiTestClient::ALL_SCOPES did not
 * request, so every call answered 401 until that scope was added to the list.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService::createBundle()), not FHIR's
 *   "searchset".
 * - An unsupported search parameter on GET /fhir/MedicationDispense does NOT produce a 400.
 *   FhirMedicationDispenseService::getAll() catches the SearchFieldException internally and
 *   returns an (empty) ProcessingResult; FhirMedicationDispenseRestController::getAll() never
 *   checks ProcessingResult::isValid() for the search route (unlike getOne()), so the request
 *   silently succeeds with HTTP 200 and an empty Bundle -- the seeded dispense disappears from
 *   the results instead of the bad parameter being rejected or ignored.
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON array
 *   (404) for an unknown one, not OperationOutcome -- same shape as every other FHIR read-one
 *   route in this codebase.
 * - `type.coding[0].code` is always "FF" (First Fill), regardless of the underlying
 *   drug_sales.dispense_type value: FhirMedicationDispenseLocalDispensaryService::
 *   mapDispenseType() hardcodes it and never reads the column.
 * - Every dispense comes back with status "completed": searchForOpenEMRRecords() forces
 *   `trans_type = 1` (sale) on every query, so no other status is ever reachable through this
 *   service today.
 */
class MedicationDispenseFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/MedicationDispense";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    private ApiTestClient $testClient;
    private MedicationDispenseFixtureManager $fixtureManager;

    /**
     * Authenticate a client. Fixtures are created per-test since most tests need to assert
     * on the specific dispense they seeded.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new MedicationDispenseFixtureManager();
    }

    /**
     * Drop every drug_sales/drug/inventory/prescription/patient/encounter row the fixture
     * manager created for this test.
     */
    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * An unfiltered search should return a collection Bundle that includes the seeded dispense
     * among its entries.
     */
    public function testSearchReturnsBundleContainingSeededDispense(): void
    {
        $record = $this->fixtureManager->createDrugSaleDispense();

        $result = $this->testClient->get(self::ENDPOINT);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertMedicationDispenseBundle($this->decodeJsonArray($result));
        $ids = array_map(static fn(array $resource): mixed => $resource['id'] ?? null, $resources);
        $this->assertContains($record['uuid'], $ids, "Unfiltered search should include the seeded dispense");
    }

    /**
     * Searching by patient should return exactly the one dispense created for that patient.
     */
    public function testSearchFilteredByPatientReturnsOnlySeededDispense(): void
    {
        $record = $this->fixtureManager->createDrugSaleDispense();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $record['patient_uuid']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertMedicationDispenseBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "The seeded patient has exactly one dispense");
        $this->assertSeededDispenseContent($resources[0], $record);
    }

    /**
     * Searching by the seeded dispense's _id should return exactly that resource.
     */
    public function testSearchByIdReturnsSeededDispense(): void
    {
        $record = $this->fixtureManager->createDrugSaleDispense();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $record['uuid']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertMedicationDispenseBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single dispense");
        $this->assertSame($record['uuid'], $resources[0]['id'] ?? null);
    }

    /**
     * An unsupported search parameter does not surface as a 400: the service swallows the
     * resulting SearchFieldException, and the controller never checks the ProcessingResult's
     * validity for this route, so the request succeeds with an empty Bundle -- the seeded
     * dispense is silently dropped from the results rather than the request being rejected.
     */
    public function testSearchWithUnsupportedParameterReturnsEmptyBundle(): void
    {
        $this->fixtureManager->createDrugSaleDispense();

        $result = $this->testClient->get(self::ENDPOINT, ['notARealSearchParam' => 'x']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(self::BUNDLE_TYPE, $bundle['type'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null, "An unsupported parameter drops all results, even the seeded one");
        $this->assertEmpty($bundle['entry'] ?? [], "An unsupported parameter drops all results, even the seeded one");
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded dispense by uuid should return the full MedicationDispense resource.
     */
    public function testGetOneReturnsMedicationDispenseResource(): void
    {
        $record = $this->fixtureManager->createDrugSaleDispense();

        $result = $this->testClient->getOne(self::ENDPOINT, $this->fixtureString($record, 'uuid'));
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("MedicationDispense", $resource['resourceType'] ?? null);
        $this->assertSame($record['uuid'], $resource['id'] ?? null);
        $this->assertSeededDispenseContent($resource, $record);
    }

    /**
     * A well-formed but unknown uuid returns 404 with an empty JSON array (not OperationOutcome).
     */
    public function testGetOneUnknownUuidReturnsNotFound(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "11111111-1111-1111-1111-111111111111");
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertEmpty($body, "404 body should be an empty JSON array");
    }

    /**
     * A malformed uuid produces 400 with validationErrors (not OperationOutcome, not 404).
     */
    public function testGetOneMalformedUuidReturnsBadRequest(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "not-a-uuid");
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
        $this->assertArrayNotHasKey('resourceType', $body, '400 body should not be a FHIR resource');
    }

    // ---------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------

    /**
     * Search without a token is rejected with 401 and an OAuth denial body.
     */
    public function testSearchUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        // Deliberately do NOT call setAuthToken().
        $this->assertOAuthUnauthorizedResponse($unauthClient->get(self::ENDPOINT));
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Base URL of the app under test, from OPENEMR_BASE_URL_API like the other API tests.
     */
    private static function baseUrl(): string
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : "https://localhost";
    }

    /**
     * Decode a JSON body and assert it is an array (object or list).
     *
     * @return array<array-key, mixed>
     */
    private function decodeJsonArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody()->getContents();
        $this->assertNotEmpty($body, "Response should have a JSON body");
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded, "Response body should be JSON");
        return $decoded;
    }

    /**
     * Assert the shape of a MedicationDispense search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertMedicationDispenseBundle(array $bundle): array
    {
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(self::BUNDLE_TYPE, $bundle['type'] ?? null);
        $this->assertArrayHasKey('entry', $bundle);
        $this->assertIsArray($bundle['entry']);

        $resources = [];
        foreach ($bundle['entry'] as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey('resource', $entry);
            $this->assertIsArray($entry['resource']);
            $this->assertSame("MedicationDispense", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Assert the elements the seeded dispense's resource must carry: status, subject,
     * context, medication text, dispense type and quantity. Coding systems/codes for
     * medicationCodeableConcept are deliberately not asserted: createDrugSaleDispense()'s
     * default drug name is reused (and its drug row NOT updated) across every test in this
     * suite and its sibling FhirMedicationDispenseUSCore8ComplianceTest, so whichever test
     * happens to run first "owns" that row's ndc/rxnorm codes.
     *
     * @param array<array-key, mixed> $resource
     * @param array<array-key, mixed> $record
     */
    private function assertSeededDispenseContent(array $resource, array $record): void
    {
        $this->assertSame('completed', $resource['status'] ?? null, "trans_type=1 (sale) always maps to status completed");

        $this->assertArrayHasKey('subject', $resource);
        $this->assertIsArray($resource['subject']);
        $this->assertSame(
            'Patient/' . $this->fixtureString($record, 'patient_uuid'),
            $resource['subject']['reference'] ?? null
        );

        $this->assertArrayHasKey('context', $resource);
        $this->assertIsArray($resource['context']);
        $this->assertSame(
            'Encounter/' . $this->fixtureString($record, 'encounter_uuid'),
            $resource['context']['reference'] ?? null
        );

        $this->assertArrayHasKey('medicationCodeableConcept', $resource);
        $this->assertIsArray($resource['medicationCodeableConcept']);
        $this->assertSame(
            $this->fixtureString($record, 'drug_name'),
            $resource['medicationCodeableConcept']['text'] ?? null
        );

        $this->assertArrayHasKey('type', $resource);
        $this->assertIsArray($resource['type']);
        $typeCodings = $resource['type']['coding'] ?? [];
        $this->assertIsArray($typeCodings);
        $this->assertArrayHasKey(0, $typeCodings);
        $this->assertIsArray($typeCodings[0]);
        $this->assertSame('FF', $typeCodings[0]['code'] ?? null, "Dispense type is always reported as First Fill (FF)");

        $this->assertArrayHasKey('quantity', $resource);
        $this->assertIsArray($resource['quantity']);
        $this->assertSame(30, $resource['quantity']['value'] ?? null);
    }

    /**
     * Read a field the fixture manager records as a string, narrowed for the callers that
     * concatenate it or pass it on as a string.
     *
     * @param array<array-key, mixed> $record
     */
    private function fixtureString(array $record, string $key): string
    {
        $value = $record[$key] ?? null;
        $this->assertIsString($value, "Fixture record should carry a string '$key'");
        return $value;
    }

    /**
     * Assert a 401 that is the OAuth denial body, not a routing or server error.
     */
    private function assertOAuthUnauthorizedResponse(ResponseInterface $response): void
    {
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $body = $this->decodeJsonArray($response);
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
}
