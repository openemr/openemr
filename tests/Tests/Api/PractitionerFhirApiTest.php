<?php

/**
 * FHIR Practitioner API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Practitioner endpoints, driven over HTTP through ApiTestClient:
 *   GET  /fhir/Practitioner          (search -> returns a Bundle)
 *   POST /fhir/Practitioner          (create -> 201)
 *   GET  /fhir/Practitioner/:uuid    (read one -> returns a single Practitioner)
 *   PUT  /fhir/Practitioner/:uuid    (update -> 200)
 *
 * Unlike Condition there IS a POST route, so each test seeds its own practitioner
 * through the API. PractitionerFixtureManager::removePractitionerFixtures() removes
 * it in tearDown: every fixture's first given name starts with "test-fixture-", and
 * that is what the cleanup matches on.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService), not FHIR's "searchset".
 * - POST and PUT answer with the stored OpenEMR row (which carries "uuid"), not with
 *   the FHIR resource.
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON
 *   array (404) for an unknown one, not OperationOutcome.
 */
class PractitionerFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Practitioner";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    private ApiTestClient $testClient;
    private PractitionerFixtureManager $fixtureManager;

    /**
     * The first FHIR practitioner fixture (Eduardo Perez, NPI 1234567890, CA address),
     * with id and meta stripped so it can be POSTed as a new resource.
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

        $this->fixtureManager = new PractitionerFixtureManager();
        $fixtures = $this->fixtureManager->getFhirPractitionerFixtures();
        $fixture = $fixtures[0] ?? null;
        $this->assertIsArray($fixture, "FHIR practitioner fixtures should not be empty");
        $this->fhirFixture = self::withStringKeys($fixture);
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    /**
     * Remove every practitioner the test created (fname LIKE "test-fixture%") and revoke the token.
     */
    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------

    /**
     * POSTing a valid Practitioner should answer 201 with the stored row, whose uuid
     * is then readable through GET /fhir/Practitioner/:uuid.
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
        $this->assertSame("Practitioner", $resource['resourceType'] ?? null);
        $this->assertSame($row['uuid'], $resource['id'] ?? null);
    }

    /**
     * A Practitioner without a name fails FHIR validation with 400 and validationErrors.
     */
    public function testPostWithoutNameReturnsBadRequest(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['name']);

        $result = $this->testClient->post(self::ENDPOINT, $invalid);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * Searching by _id should return a collection Bundle with exactly the seeded
     * practitioner, carrying the name, NPI identifier and active flag that were POSTed.
     */
    public function testSearchByIdReturnsSeededPractitioner(): void
    {
        $uuid = $this->createPractitioner();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $uuid]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertPractitionerBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single practitioner");
        $this->assertSame($uuid, $resources[0]['id'] ?? null);
        $this->assertSeededPractitionerContent($resources[0]);
    }

    /**
     * Searching by the seeded given name (mapped to users.fname) should find it.
     */
    public function testSearchByGivenNameReturnsSeededPractitioner(): void
    {
        $uuid = $this->createPractitioner();

        $result = $this->testClient->get(self::ENDPOINT, ['given' => 'test-fixture-Eduardo']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertPractitionerBundle($this->decodeJsonArray($result));
        $this->assertNotEmpty($resources, "Search by given name should return at least the seeded practitioner");
        $ids = array_map(static fn(array $resource): mixed => $resource['id'] ?? null, $resources);
        $this->assertContains($uuid, $ids, "Search by given name should include the seeded practitioner");
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
     * Reading the seeded practitioner by uuid should return a full Practitioner resource.
     */
    public function testGetOneReturnsPractitionerResource(): void
    {
        $uuid = $this->createPractitioner();

        $result = $this->testClient->getOne(self::ENDPOINT, $uuid);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Practitioner", $resource['resourceType'] ?? null);
        $this->assertSame($uuid, $resource['id'] ?? null);
        $this->assertSeededPractitionerContent($resource);

        $this->assertArrayHasKey('address', $resource);
        $this->assertIsArray($resource['address']);
        $this->assertIsArray($resource['address'][0] ?? null);
        $this->assertSame('CA', $resource['address'][0]['state'] ?? null);

        $this->assertArrayHasKey('telecom', $resource);
        $this->assertIsArray($resource['telecom']);
        $telecomValues = [];
        foreach ($resource['telecom'] as $telecom) {
            $this->assertIsArray($telecom);
            $telecomValues[] = $telecom['value'] ?? null;
        }
        $this->assertContains('(619) 555-7821', $telecomValues, "Seeded work phone should be exposed as a telecom");
        $this->assertContains('info@pennfirm.com', $telecomValues, "Seeded email should be exposed as a telecom");
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
     * PUT with a changed family name should answer 200 and the change must be visible
     * on the next read.
     */
    public function testPutUpdatesFamilyName(): void
    {
        $uuid = $this->createPractitioner();

        $updated = $this->fhirFixture;
        $this->assertIsArray($updated['name']);
        $this->assertIsArray($updated['name'][0]);
        $updated['name'][0]['family'] = 'Smithers';

        $result = $this->testClient->put(self::ENDPOINT, $uuid, $updated);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode(), "PUT should return 200 OK");
        $row = $this->decodeJsonArray($result);
        $this->assertArrayNotHasKey('validationErrors', $row);

        $readBack = $this->decodeJsonArray($this->testClient->getOne(self::ENDPOINT, $uuid));
        $this->assertIsArray($readBack['name'] ?? null);
        $this->assertIsArray($readBack['name'][0] ?? null);
        $this->assertSame('Smithers', $readBack['name'][0]['family'] ?? null, "PUT should have changed the family name");
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
     * POST the fixture and return the uuid of the created practitioner.
     */
    private function createPractitioner(): string
    {
        $result = $this->testClient->post(self::ENDPOINT, $this->fhirFixture);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "Seeding practitioner should return 201");
        $row = $this->decodeJsonArray($result);
        $this->assertIsString($row['uuid'] ?? null, "Seeding practitioner should return its uuid");
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
     * Assert the shape of a Practitioner search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertPractitionerBundle(array $bundle): array
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
            $this->assertSame("Practitioner", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Assert the elements every read of the seeded fixture must carry:
     * official name, US NPI identifier and active flag.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededPractitionerContent(array $resource): void
    {
        $this->assertTrue($resource['active'] ?? null, "Seeded practitioner is active");

        $this->assertArrayHasKey('name', $resource);
        $this->assertIsArray($resource['name']);
        $this->assertIsArray($resource['name'][0] ?? null);
        $this->assertSame('Perez', $resource['name'][0]['family'] ?? null);
        $this->assertIsArray($resource['name'][0]['given'] ?? null);
        $this->assertSame('test-fixture-Eduardo', $resource['name'][0]['given'][0] ?? null);

        $this->assertArrayHasKey('identifier', $resource);
        $this->assertIsArray($resource['identifier']);
        $this->assertIsArray($resource['identifier'][0] ?? null);
        $this->assertSame('http://hl7.org/fhir/sid/us-npi', $resource['identifier'][0]['system'] ?? null);
        $this->assertSame('1234567890', $resource['identifier'][0]['value'] ?? null);
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
