<?php

/**
 * FHIR ValueSet API tests.
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
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR ValueSet endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/ValueSet        (search -> returns a Bundle)
 *   GET /fhir/ValueSet/:id    (read one -> returns a single ValueSet)
 *
 * A ValueSet is either the "appointment-type" set (calendar categories of type 0) or one
 * list from list_options (the list's option_id in list_id "lists" is the ValueSet id, its
 * options are the concepts). Each test seeds its own list; tearDown removes every
 * list_options row whose list or option id starts with the fixture prefix.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService), not FHIR's "searchset".
 * - An unknown id on read-one returns an empty JSON array (404), not OperationOutcome.
 * - A list without options is not exposed as a ValueSet.
 */
class ValueSetFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/ValueSet";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Id of the ValueSet built from the calendar categories. */
    private const APPOINTMENT_TYPE = "appointment-type";

    /** Id of the seeded list; also the prefix tearDown cleans up on. */
    private const LIST_ID = "test-fixture-valueset";

    /** Id of a seeded list that has no options. */
    private const EMPTY_LIST_ID = "test-fixture-valueset-empty";

    /** Options of the seeded list, in seq order: option_id => title. */
    private const LIST_OPTIONS = ['beta' => 'Beta', 'alpha' => 'Alpha'];

    private ApiTestClient $testClient;

    /**
     * Seed the two fixture lists and authenticate a client.
     */
    protected function setUp(): void
    {
        $this->removeFixtureLists();
        $this->seedList(self::LIST_ID, "Test Fixture ValueSet", self::LIST_OPTIONS);
        $this->seedList(self::EMPTY_LIST_ID, "Test Fixture Empty ValueSet", []);

        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    /**
     * Remove the fixture lists and revoke the token.
     */
    protected function tearDown(): void
    {
        $this->removeFixtureLists();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * A search without parameters returns every ValueSet: the appointment types and each
     * non-empty list, including the seeded one.
     */
    public function testSearchWithoutParametersReturnsAllValueSets(): void
    {
        $result = $this->testClient->get(self::ENDPOINT);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->idsOf($this->assertValueSetBundle($this->decodeJsonArray($result)));
        $this->assertContains(self::APPOINTMENT_TYPE, $ids);
        $this->assertContains(self::LIST_ID, $ids);
        $this->assertNotContains(self::EMPTY_LIST_ID, $ids, "A list without options is not a ValueSet");
    }

    /**
     * Searching by _id returns exactly the seeded list, with its options as concepts.
     */
    public function testSearchByIdReturnsOnlyThatList(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => self::LIST_ID]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertValueSetBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single ValueSet");
        $this->assertSame(self::LIST_ID, $resources[0]['id'] ?? null);
        $this->assertSame(self::LIST_OPTIONS, $this->conceptsOf($resources[0]));
    }

    /**
     * Searching by _id=appointment-type returns only the calendar-category ValueSet, whose
     * concepts are the categories of type 0.
     */
    public function testSearchByAppointmentTypeIdReturnsCalendarCategories(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => self::APPOINTMENT_TYPE]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertValueSetBundle($this->decodeJsonArray($result));
        $this->assertCount(1, $resources, "Search by _id should return a single ValueSet");
        $this->assertSame(self::APPOINTMENT_TYPE, $resources[0]['id'] ?? null);
        $this->assertSame($this->appointmentCategoryConcepts(), $this->conceptsOf($resources[0]));
    }

    /**
     * Comma-separated _id values are an OR: both ValueSets come back.
     */
    public function testSearchByTwoIdsReturnsBoth(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => self::LIST_ID . ',' . self::APPOINTMENT_TYPE]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->idsOf($this->assertValueSetBundle($this->decodeJsonArray($result)));
        sort($ids);
        $this->assertSame([self::APPOINTMENT_TYPE, self::LIST_ID], $ids);
    }

    /**
     * An _id that matches no list returns an empty Bundle with HTTP 200, not 404.
     */
    public function testSearchByUnknownIdReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => self::LIST_ID . '-missing']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame([], $this->assertValueSetBundle($bundle));
        $this->assertSame(0, $bundle['total'] ?? null, "No-match search should have total 0");
    }

    /**
     * A list without options is not returned even when asked for by _id.
     */
    public function testSearchByIdOfEmptyListReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['_id' => self::EMPTY_LIST_ID]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $this->assertSame([], $this->assertValueSetBundle($this->decodeJsonArray($result)));
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded list by id returns a single ValueSet with its options as concepts.
     */
    public function testGetOneReturnsList(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, self::LIST_ID);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("ValueSet", $resource['resourceType'] ?? null);
        $this->assertSame(self::LIST_ID, $resource['id'] ?? null);
        $this->assertSame(self::LIST_OPTIONS, $this->conceptsOf($resource));
    }

    /**
     * Reading appointment-type returns the calendar-category ValueSet.
     */
    public function testGetOneReturnsAppointmentType(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, self::APPOINTMENT_TYPE);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("ValueSet", $resource['resourceType'] ?? null);
        $this->assertSame(self::APPOINTMENT_TYPE, $resource['id'] ?? null);
        $this->assertSame($this->appointmentCategoryConcepts(), $this->conceptsOf($resource));
    }

    /**
     * An unknown id returns 404 with an empty JSON array (not OperationOutcome).
     */
    public function testGetOneUnknownIdReturnsNotFound(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, self::LIST_ID . '-missing');
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $this->assertSame([], $this->decodeJsonArray($result), "404 body should be an empty JSON array");
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
        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        $this->assertOAuthUnauthorizedResponse($unauthClient->getOne(self::ENDPOINT, self::LIST_ID));
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
     * Register a list in list_options (as an option of list "lists") and add its options.
     *
     * @param array<string, string> $options option_id => title, inserted in this order
     */
    private function seedList(string $listId, string $title, array $options): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO list_options (list_id, option_id, title, seq) VALUES ('lists', ?, ?, 1)",
            [$listId, $title]
        );
        $seq = 10;
        foreach ($options as $optionId => $optionTitle) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options (list_id, option_id, title, seq) VALUES (?, ?, ?, ?)",
                [$listId, $optionId, $optionTitle, $seq]
            );
            $seq += 10;
        }
    }

    /**
     * Delete the fixture lists: their "lists" entries and their options.
     */
    private function removeFixtureLists(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM list_options WHERE (list_id = 'lists' AND option_id LIKE ?) OR list_id LIKE ?",
            [self::LIST_ID . '%', self::LIST_ID . '%']
        );
    }

    /**
     * The concepts the appointment-type ValueSet should carry, read from the database:
     * categories of type 0, pc_constant_id => pc_catname, in table order.
     *
     * @return array<string, string>
     */
    private function appointmentCategoryConcepts(): array
    {
        $concepts = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT pc_constant_id, pc_catname FROM openemr_postcalendar_categories WHERE pc_cattype = 0 ORDER BY pc_catid"
        );
        foreach ($rows as $row) {
            $this->assertIsString($row['pc_constant_id'] ?? null);
            $this->assertIsString($row['pc_catname'] ?? null);
            $concepts[$row['pc_constant_id']] = $row['pc_catname'];
        }
        $this->assertNotEmpty($concepts, "The seeded database should have calendar categories of type 0");
        return $concepts;
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
     * Assert the shape of a ValueSet search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertValueSetBundle(array $bundle): array
    {
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(self::BUNDLE_TYPE, $bundle['type'] ?? null);

        $entries = $bundle['entry'] ?? [];
        $this->assertIsArray($entries);
        $resources = [];
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $this->assertIsArray($entry['resource'] ?? null);
            $this->assertSame("ValueSet", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Ids of the given resources, in bundle order.
     *
     * @param list<array<array-key, mixed>> $resources
     * @return list<string>
     */
    private function idsOf(array $resources): array
    {
        $ids = [];
        foreach ($resources as $resource) {
            $this->assertIsString($resource['id'] ?? null);
            $ids[] = $resource['id'];
        }
        return $ids;
    }

    /**
     * Concepts of a ValueSet's single compose.include, as code => display in order.
     *
     * @param array<array-key, mixed> $resource
     * @return array<string, string>
     */
    private function conceptsOf(array $resource): array
    {
        $this->assertIsArray($resource['compose'] ?? null);
        $this->assertIsArray($resource['compose']['include'] ?? null);
        $this->assertCount(1, $resource['compose']['include']);
        $this->assertIsArray($resource['compose']['include'][0] ?? null);
        $this->assertIsArray($resource['compose']['include'][0]['concept'] ?? null);

        $concepts = [];
        foreach ($resource['compose']['include'][0]['concept'] as $concept) {
            $this->assertIsArray($concept);
            $this->assertIsString($concept['code'] ?? null);
            $this->assertIsString($concept['display'] ?? null);
            $concepts[$concept['code']] = $concept['display'];
        }
        return $concepts;
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
