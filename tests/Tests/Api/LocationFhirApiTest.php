<?php

/**
 * FHIR Location API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Location endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/Location          (search -> returns a Bundle)
 *   GET /fhir/Location/:uuid    (read one -> returns a single Location)
 *
 * There is no POST /fhir/Location route. Locations are a read-only view over facilities,
 * practitioner addresses and patient home addresses, each keyed by its own uuid in
 * uuid_mapping (created on demand by LocationService). So setUp seeds facilities through
 * FacilityFixtureManager and the tests find the seeded Location by name over HTTP; tearDown
 * removes the fixtures and their uuid_mapping rows.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService), not FHIR's "searchset".
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON
 *   array (404) for an unknown one, not OperationOutcome.
 * - Every Location has status "active"; a facility Location carries the facility's
 *   address and telecoms. managingOrganization, when present, is the system's primary
 *   business entity rather than the facility itself.
 */
class LocationFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Location";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Name of the first facility fixture; the prefix is what FacilityFixtureManager cleans up on. */
    private const SEEDED_NAME = "test-fixture-Your Clinic Name Here";

    private ApiTestClient $testClient;
    private FacilityFixtureManager $fixtureManager;

    /**
     * Authenticate a client and install the facility fixtures the Locations are derived from.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FacilityFixtureManager();
        $this->fixtureManager->installFacilityFixtures();
    }

    /**
     * Drop the Location uuids minted for the seeded facilities, then the facilities themselves.
     */
    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE um FROM uuid_mapping um JOIN facility f ON f.uuid = um.target_uuid"
            . " WHERE um.resource = 'Location' AND f.name LIKE ?",
            ['test-fixture%']
        );
        $this->fixtureManager->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * Searching by the seeded facility name should return a collection Bundle with exactly
     * one Location carrying the facility's name, address and phone.
     */
    public function testSearchByNameReturnsSeededFacilityLocation(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['name' => self::SEEDED_NAME]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertLocationBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Exactly one Location should carry the seeded facility name");
        $this->assertIsString($resources[0]['id'] ?? null, "The Location should have a uuid minted in uuid_mapping");
        $this->assertSeededLocationContent($resources[0]);
    }

    /**
     * Searching by the seeded Location's _id should return exactly that Location.
     */
    public function testSearchByIdReturnsSeededLocation(): void
    {
        $uuid = $this->seededLocationUuid();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $uuid]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertLocationBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single Location");
        $this->assertSame($uuid, $resources[0]['id'] ?? null);
        $this->assertSeededLocationContent($resources[0]);
    }

    /**
     * Searching by address-city (mapped to facility.city) should include the seeded Location.
     */
    public function testSearchByCityReturnsSeededLocation(): void
    {
        $uuid = $this->seededLocationUuid();

        $result = $this->testClient->get(self::ENDPOINT, ['address-city' => 'San Diego']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertLocationBundle($this->decodeJsonArray($result));
        $ids = array_map(static fn(array $resource): mixed => $resource['id'] ?? null, $resources);
        $this->assertContains($uuid, $ids, "Search by address-city should include the seeded Location");
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
     * Reading the seeded Location by uuid should return the full Location resource.
     */
    public function testGetOneReturnsLocationResource(): void
    {
        $uuid = $this->seededLocationUuid();

        $result = $this->testClient->getOne(self::ENDPOINT, $uuid);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Location", $resource['resourceType'] ?? null);
        $this->assertSame($uuid, $resource['id'] ?? null);
        $this->assertSeededLocationContent($resource);
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
     * Read-one without a token is rejected with 401 and an OAuth denial body.
     */
    public function testGetOneUnauthorizedReturns401(): void
    {
        $uuid = $this->seededLocationUuid();

        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        $this->assertOAuthUnauthorizedResponse($unauthClient->getOne(self::ENDPOINT, $uuid));
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
     * Find the seeded facility's Location over HTTP and return its uuid.
     */
    private function seededLocationUuid(): string
    {
        $result = $this->testClient->get(self::ENDPOINT, ['name' => self::SEEDED_NAME]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode(), "Looking up the seeded Location should succeed");
        $resources = $this->assertLocationBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Exactly one Location should carry the seeded facility name");
        $this->assertIsString($resources[0]['id'] ?? null, "The seeded Location should have a uuid");
        return $resources[0]['id'];
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
     * Assert the shape of a Location search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertLocationBundle(array $bundle): array
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
            $this->assertSame("Location", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Assert the elements the seeded facility's Location must carry: name, active status,
     * the San Diego address and the facility phone. managingOrganization is deliberately
     * not asserted: it points at the system's primary business entity, which depends on
     * the database the suite runs against, not on the seeded fixture.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededLocationContent(array $resource): void
    {
        $this->assertSame(self::SEEDED_NAME, $resource['name'] ?? null);
        $this->assertSame('active', $resource['status'] ?? null, "Every OpenEMR Location is reported active");

        $this->assertArrayHasKey('address', $resource);
        $this->assertIsArray($resource['address']);
        $this->assertSame('San Diego', $resource['address']['city'] ?? null);
        $this->assertSame('CA', $resource['address']['state'] ?? null);
        $this->assertSame('90210', $resource['address']['postalCode'] ?? null);

        $this->assertArrayHasKey('telecom', $resource);
        $this->assertIsArray($resource['telecom']);
        $telecomValues = [];
        foreach ($resource['telecom'] as $telecom) {
            $this->assertIsArray($telecom);
            $telecomValues[] = $telecom['value'] ?? null;
        }
        $this->assertContains('(619) 555-4859', $telecomValues, "Facility phone should be exposed as a telecom");
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
