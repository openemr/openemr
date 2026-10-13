<?php

/**
 * FHIR Group API tests.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Group endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/Group        (search -> returns a Bundle)
 *   GET /fhir/Group/:uuid  (read one -> returns a single Group)
 *
 * A Group is computed, not stored: one per practitioner with an NPI, listing the patients whose
 * `providerID` is that practitioner (GroupService::searchPatientProviderGroups()). Its id is a
 * `uuid_mapping` row (resource 'Group') pointing at the practitioner's `users.uuid`.
 * GroupService creates missing mappings on construction; the tests seed their own so the ids
 * are known up front, and the service then leaves them alone.
 *
 * Each test that needs data seeds a practitioner with an NPI and two patients, and a
 * practitioner without an NPI with one patient. tearDown removes them and their mappings.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService::createBundle()), not FHIR's
 *   "searchset".
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON array
 *   (404) for an unknown one, not OperationOutcome -- same shape as every other FHIR read-one
 *   route in this codebase.
 */
class GroupFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Group";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Prefix of every seeded name, so leftovers are easy to spot. */
    private const TAG = "test-fixture-group";

    /** A valid NPI (Luhn check digit), the Group source filter only checks it is not empty. */
    private const NPI = "1234567893";

    private ApiTestClient $testClient;

    /** @var list<string> uuid_registry entries (binary) created by the seed helpers */
    private array $registeredUuids = [];

    /** @var list<int> users rows inserted by seedGroups() */
    private array $userIds = [];

    /** @var list<int> patient_data pids inserted by seedGroups() */
    private array $pids = [];

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    /**
     * Drop the seeded patients, the practitioners and every uuid_mapping row that points at
     * them (seeded or created by GroupService), then the uuids registered for them.
     */
    protected function tearDown(): void
    {
        foreach ($this->pids as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->pids = [];
        foreach ($this->userIds as $userId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE um FROM uuid_mapping um JOIN users u ON u.uuid = um.target_uuid WHERE u.id = ?",
                [$userId]
            );
            QueryUtils::sqlStatementThrowException("DELETE FROM users WHERE id = ?", [$userId]);
        }
        $this->userIds = [];
        foreach ($this->registeredUuids as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid = ?", [$uuid]);
        }
        $this->registeredUuids = [];

        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * An unfiltered search includes the practitioner's Group with exactly its two patients, and
     * no Group for the practitioner without an NPI.
     */
    public function testSearchIncludesGroupOfPractitionerWithNpi(): void
    {
        $seed = $this->seedGroups();

        $result = $this->testClient->get(self::ENDPOINT);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $groups = $this->groupsById($this->assertGroupBundle($this->decodeJsonArray($result)));
        $this->assertArrayNotHasKey($seed['group_without_npi'], $groups, "A practitioner without an NPI has no Group");
        $this->assertArrayHasKey($seed['group'], $groups);
        $this->assertEqualsCanonicalizing(
            ["Patient/" . $seed['patient_1'], "Patient/" . $seed['patient_2']],
            $this->memberReferences($groups[$seed['group']])
        );
    }

    /**
     * Search parameters return exactly the expected Groups.
     *
     * @param string $param search parameter
     * @param string $valueKey key of the seed array holding the value
     * @param list<string> $expected keys of the seed array
     */
    #[DataProvider('searchProvider')]
    public function testSearchParameters(string $param, string $valueKey, array $expected): void
    {
        $seed = $this->seedGroups();

        $result = $this->testClient->get(self::ENDPOINT, [$param => $seed[$valueKey]]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = array_keys($this->groupsById($this->assertGroupBundle($this->decodeJsonArray($result))));
        $this->assertSame(array_map(static fn(string $key): string => $seed[$key], $expected), $ids);
    }

    /**
     * @return array<string, array{string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function searchProvider(): array
    {
        return [
            '_id of the Group' => ['_id', 'group', ['group']],
            '_id of the practitioner without an NPI' => ['_id', 'group_without_npi', []],
            'patient in the Group' => ['patient', 'patient_1', ['group']],
            'patient of the practitioner without an NPI' => ['patient', 'patient_3', []],
        ];
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the Group returns its name (practitioner's name + "Patients") and its members.
     */
    public function testGetOneReturnsGroup(): void
    {
        $seed = $this->seedGroups();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['group']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Group", $resource['resourceType'] ?? null);
        $this->assertSame($seed['group'], $resource['id'] ?? null);
        $this->assertSame(self::TAG . " Provider Patients", $resource['name'] ?? null);
        $this->assertEqualsCanonicalizing(
            ["Patient/" . $seed['patient_1'], "Patient/" . $seed['patient_2']],
            $this->memberReferences($resource)
        );
    }

    /**
     * The mapping of a practitioner without an NPI is not a Group: 404.
     */
    public function testGetOnePractitionerWithoutNpiReturnsNotFound(): void
    {
        $seed = $this->seedGroups();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['group_without_npi']);
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());
    }

    /**
     * A well-formed but unknown uuid returns 404 with an empty JSON array (not OperationOutcome).
     */
    public function testGetOneUnknownUuidReturnsNotFound(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "11111111-1111-1111-1111-111111111111");
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $this->assertSame([], $this->decodeJsonArray($result), "404 body should be an empty JSON array");
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
     * Seed a practitioner with an NPI and two patients, and a practitioner without an NPI with
     * one patient, each with its Group mapping, and return the uuids the assertions need.
     *
     * @return array{group: string, group_without_npi: string, patient_1: string, patient_2: string, patient_3: string}
     */
    private function seedGroups(): array
    {
        [$providerId, $group] = $this->insertPractitioner("Provider", self::NPI);
        [$otherProviderId, $groupWithoutNpi] = $this->insertPractitioner("NoNpi", "");

        return [
            'group' => $group,
            'group_without_npi' => $groupWithoutNpi,
            'patient_1' => $this->insertPatient("One", $providerId),
            'patient_2' => $this->insertPatient("Two", $providerId),
            'patient_3' => $this->insertPatient("Three", $otherProviderId),
        ];
    }

    /**
     * Insert a practitioner and its Group uuid_mapping row.
     *
     * @return array{int, string} users.id and the Group uuid as a string
     */
    private function insertPractitioner(string $lname, string $npi): array
    {
        $userUuid = $this->registerUuid(['table_name' => 'users']);
        $userId = QueryUtils::sqlInsert(
            "INSERT INTO users (uuid, username, fname, lname, npi, active, authorized) VALUES (?, ?, ?, ?, ?, 1, 1)",
            [$userUuid, self::TAG . '-' . $lname, self::TAG, $lname, $npi]
        );
        $this->userIds[] = $userId;

        $groupUuid = $this->registerUuid(['table_name' => 'uuid_mapping', 'mapped' => true]);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO uuid_mapping (uuid, resource, `table`, target_uuid, created) VALUES (?, 'Group', 'users', ?, NOW())",
            [$groupUuid, $userUuid]
        );

        return [$userId, UuidRegistry::uuidToString($groupUuid)];
    }

    /**
     * Insert a patient whose provider is the given practitioner.
     *
     * @return string The patient's uuid as a string
     */
    private function insertPatient(string $lname, int $providerId): string
    {
        $nextPid = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($nextPid);
        $pid = (int) $nextPid;
        $uuid = $this->registerUuid(['table_name' => 'patient_data']);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, providerID) VALUES (?, ?, ?, ?, ?)",
            [$pid, $uuid, self::TAG, $lname, $providerId]
        );
        $this->pids[] = $pid;
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Create a uuid in uuid_registry for the given table and remember it for tearDown.
     *
     * @param array<string, string|bool> $associations UuidRegistry constructor associations
     * @return string The uuid as bytes
     */
    private function registerUuid(array $associations): string
    {
        $uuid = (new UuidRegistry($associations))->createUuid();
        $this->registeredUuids[] = $uuid;
        return $uuid;
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
     * Assert the shape of a Group search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertGroupBundle(array $bundle): array
    {
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(self::BUNDLE_TYPE, $bundle['type'] ?? null);

        $entries = $bundle['entry'] ?? [];
        $this->assertIsArray($entries);
        $this->assertSame(count($entries), $bundle['total'] ?? null, "Bundle total should count its entries");

        $resources = [];
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey('resource', $entry);
            $this->assertIsArray($entry['resource']);
            $this->assertSame("Group", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * Index Group resources by id.
     *
     * @param list<array<array-key, mixed>> $resources
     * @return array<string, array<array-key, mixed>>
     */
    private function groupsById(array $resources): array
    {
        $groups = [];
        foreach ($resources as $resource) {
            $id = $resource['id'] ?? null;
            $this->assertIsString($id);
            $groups[$id] = $resource;
        }
        return $groups;
    }

    /**
     * The entity references of a Group's members.
     *
     * @param array<array-key, mixed> $group
     * @return list<mixed>
     */
    private function memberReferences(array $group): array
    {
        $members = $group['member'] ?? [];
        $this->assertIsArray($members);
        $references = [];
        foreach ($members as $member) {
            $this->assertIsArray($member);
            $this->assertArrayHasKey('entity', $member);
            $this->assertIsArray($member['entity']);
            $references[] = $member['entity']['reference'] ?? null;
        }
        return $references;
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
