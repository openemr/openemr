<?php

/**
 * FHIR Organization API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Organization endpoints, driven over HTTP through ApiTestClient:
 *   GET  /fhir/Organization          (search -> returns a Bundle)
 *   POST /fhir/Organization          (create -> 201, stored as a facility)
 *   GET  /fhir/Organization/:uuid    (read one -> returns a single Organization)
 *   PUT  /fhir/Organization/:uuid    (update -> 200)
 *
 * Each test seeds its own organization through POST. FacilityFixtureManager::removeFixtures()
 * removes it in tearDown: every fixture name starts with "test-fixture-", and the
 * cleanup matches on that prefix, so the updated name in the PUT test keeps it too.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService), not FHIR's "searchset".
 * - POST and PUT answer with the stored OpenEMR row (which carries "uuid"), not with
 *   the FHIR resource.
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON
 *   array (404) for an unknown one, not OperationOutcome.
 * - Facilities have no inactive state, so "active" is always true.
 */
class OrganizationFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Organization";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Name of the seeded fixture; the prefix is what FacilityFixtureManager cleans up on. */
    private const SEEDED_NAME = "test-fixture-Health Level Seven International";

    private ApiTestClient $testClient;
    private FacilityFixtureManager $fixtureManager;

    /**
     * The second FHIR facility fixture (Health Level Seven International, NPI 1039294177,
     * Ann Arbor MI, work phone and email), with id and meta stripped so it can be POSTed.
     *
     * @var array<string, mixed>
     */
    private array $fhirFixture;

    /**
     * Authenticate a client and load the fixture that every test POSTs as its own seed.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FacilityFixtureManager();
        $fixtures = $this->fixtureManager->getFhirFacilityFixtures();
        $fixture = $fixtures[1] ?? null;
        $this->assertIsArray($fixture, "FHIR facility fixtures should have at least two entries");
        $this->fhirFixture = self::withStringKeys($fixture);
        $this->assertSame(self::SEEDED_NAME, $this->fhirFixture['name'] ?? null, "Fixture 1 should be HL7 International");
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    /**
     * Remove every facility the test created (name LIKE "test-fixture%") and revoke the token.
     */
    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------

    /**
     * POSTing a valid Organization should answer 201 with the stored row, whose uuid
     * is then readable through GET /fhir/Organization/:uuid.
     */
    public function testPostReturnsCreatedAndResourceIsReadable(): void
    {
        $result = $this->testClient->post(self::ENDPOINT, $this->fhirFixture);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "POST should return 201 Created");

        $row = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('uuid', $row, "POST should answer with the stored row including its uuid");
        $this->assertIsString($row['uuid']);
        $this->assertArrayNotHasKey('validationErrors', $row);

        $readBack = $this->testClient->getOne(self::ENDPOINT, $row['uuid']);
        $this->assertSame(Response::HTTP_OK, $readBack->getStatusCode());
        $resource = $this->decodeJsonArray($readBack);
        $this->assertSame("Organization", $resource['resourceType'] ?? null);
        $this->assertSame($row['uuid'], $resource['id'] ?? null);
    }

    /**
     * An Organization without a name fails FHIR validation with 400.
     */
    public function testPostWithoutNameReturnsBadRequest(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['name']);

        $result = $this->testClient->post(self::ENDPOINT, $invalid);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertNotEmpty($body, "400 body should describe the validation failure");
        $this->assertArrayNotHasKey('uuid', $body, "Nothing should have been stored");
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * Searching by _id should return a collection Bundle with exactly the seeded
     * organization, carrying the name, NPI identifier and address that were POSTed.
     */
    public function testSearchByIdReturnsSeededOrganization(): void
    {
        $uuid = $this->createOrganization();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $uuid]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertOrganizationBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single organization");
        $this->assertSame($uuid, $resources[0]['id'] ?? null);
        $this->assertSeededOrganizationContent($resources[0]);
    }

    /**
     * Searching by the seeded name (mapped to facility.name) should find it.
     */
    public function testSearchByNameReturnsSeededOrganization(): void
    {
        $uuid = $this->createOrganization();

        $result = $this->testClient->get(self::ENDPOINT, ['name' => self::SEEDED_NAME]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertOrganizationBundle($this->decodeJsonArray($result));
        $this->assertNotEmpty($resources, "Search by name should return at least the seeded organization");
        $ids = array_map(static fn(array $resource): mixed => $resource['id'] ?? null, $resources);
        $this->assertContains($uuid, $ids, "Search by name should include the seeded organization");
    }

    /**
     * A search that matches nothing should return an empty Bundle with HTTP 200, not 404.
     */
    public function testSearchWithNoMatchReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => '00000000-0000-0000-0000-000000000000']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(self::BUNDLE_TYPE, $bundle['type'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null, "No-match search should have total 0");
        $this->assertEmpty($bundle['entry'] ?? [], "No-match search should have no entries");
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded organization by uuid should return a full Organization resource.
     */
    public function testGetOneReturnsOrganizationResource(): void
    {
        $uuid = $this->createOrganization();

        $result = $this->testClient->getOne(self::ENDPOINT, $uuid);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Organization", $resource['resourceType'] ?? null);
        $this->assertSame($uuid, $resource['id'] ?? null);
        $this->assertSeededOrganizationContent($resource);

        $this->assertArrayHasKey('telecom', $resource);
        $this->assertIsArray($resource['telecom']);
        $telecomValues = [];
        foreach ($resource['telecom'] as $telecom) {
            $this->assertIsArray($telecom);
            $telecomValues[] = $telecom['value'] ?? null;
        }
        $this->assertContains('(+1) 734-677-7777', $telecomValues, "Seeded work phone should be exposed as a telecom");
        $this->assertContains('hq@HL7.org', $telecomValues, "Seeded email should be exposed as a telecom");
    }

    /**
     * A malformed uuid produces 400 with validationErrors (not OperationOutcome, not 404).
     */
    public function testGetOneMalformedUuidReturnsBadRequest(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "not-a-uuid");
        $this->assertValidationErrorResponse($result);
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

    // ---------------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------------

    /**
     * PUT with a changed name should answer 200 and the change must be visible on the
     * next read. The new name keeps the "test-fixture-" prefix so tearDown still finds it.
     */
    public function testPutUpdatesName(): void
    {
        $uuid = $this->createOrganization();

        $updated = $this->fhirFixture;
        $updated['name'] = 'test-fixture-Glenmark Clinic';

        $result = $this->testClient->put(self::ENDPOINT, $uuid, $updated);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode(), "PUT should return 200 OK");
        $row = $this->decodeJsonArray($result);
        $this->assertArrayNotHasKey('validationErrors', $row);

        $readBack = $this->decodeJsonArray($this->testClient->getOne(self::ENDPOINT, $uuid));
        $this->assertSame('test-fixture-Glenmark Clinic', $readBack['name'] ?? null, "PUT should have changed the name");
    }

    /**
     * PUT against a malformed uuid produces 400 with validationErrors.
     */
    public function testPutMalformedUuidReturnsBadRequest(): void
    {
        $result = $this->testClient->put(self::ENDPOINT, "not-a-uuid", $this->fhirFixture);
        $this->assertValidationErrorResponse($result);
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

    /**
     * Create without a token is rejected with 401 before anything is written.
     */
    public function testPostUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        $this->assertOAuthUnauthorizedResponse($unauthClient->post(self::ENDPOINT, $this->fhirFixture));
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
     * Narrow an untyped fixture array to string keys; FHIR element names are always strings.
     *
     * @param array<array-key, mixed> $values
     * @return array<string, mixed>
     */
    private static function withStringKeys(array $values): array
    {
        $typed = [];
        foreach ($values as $key => $value) {
            self::assertIsString($key, "FHIR fixture keys should be element names");
            $typed[$key] = $value;
        }
        return $typed;
    }

    /**
     * POST the fixture and return the uuid of the created organization.
     */
    private function createOrganization(): string
    {
        $result = $this->testClient->post(self::ENDPOINT, $this->fhirFixture);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "Seeding organization should return 201");
        $row = $this->decodeJsonArray($result);
        $this->assertIsString($row['uuid'] ?? null, "Seeding organization should return its uuid");
        return $row['uuid'];
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
     * Assert the shape of an Organization search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertOrganizationBundle(array $bundle): array
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
            $this->assertSame("Organization", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Assert the elements every read of the seeded fixture must carry:
     * name, US NPI identifier, active flag and the Ann Arbor address.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededOrganizationContent(array $resource): void
    {
        $this->assertSame(self::SEEDED_NAME, $resource['name'] ?? null);
        $this->assertTrue($resource['active'] ?? null, "Facility-backed organizations are always active");

        $this->assertArrayHasKey('identifier', $resource);
        $this->assertIsArray($resource['identifier']);
        $this->assertIsArray($resource['identifier'][0] ?? null);
        $this->assertSame('http://hl7.org/fhir/sid/us-npi', $resource['identifier'][0]['system'] ?? null);
        $this->assertSame('1039294177', $resource['identifier'][0]['value'] ?? null);

        $this->assertArrayHasKey('address', $resource);
        $this->assertIsArray($resource['address']);
        $this->assertIsArray($resource['address'][0] ?? null);
        $this->assertSame('Ann Arbor', $resource['address'][0]['city'] ?? null);
        $this->assertSame('MI', $resource['address'][0]['state'] ?? null);
        $this->assertSame('48104', $resource['address'][0]['postalCode'] ?? null);
    }

    /**
     * Assert a 400 whose body carries validationErrors, the shape RestControllerHelper uses.
     */
    private function assertValidationErrorResponse(ResponseInterface $response): void
    {
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeJsonArray($response);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
        $this->assertArrayNotHasKey('resourceType', $body, '400 body should not be a FHIR resource');
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
