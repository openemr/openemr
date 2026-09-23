<?php

/**
 * FHIR Specimen API tests.
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
use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Specimen endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/Specimen        (search -> returns a Bundle)
 *   GET /fhir/Specimen/:uuid  (read one -> returns a single Specimen)
 *
 * There is no POST/PUT route. FhirSpecimenService reads `procedure_specimen` rows joined to
 * their `procedure_order` and patient. `deleted = 1` maps to status "entered-in-error", and a
 * search without `status` only returns specimens that are not deleted.
 *
 * Each test that needs data seeds one order (through ProcedureOrderFixtureManager, which also
 * brings the patient, encounter, lab and ordering practitioner) with two specimens, one
 * available and one deleted, plus a second patient with an order and one specimen of its own.
 * tearDown removes them.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - A deleted specimen is not readable by id (404), although `status=entered-in-error` finds it.
 * - Search bundles use type "collection" (FhirResourcesService::createBundle()), not FHIR's
 *   "searchset".
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON array
 *   (404) for an unknown one, not OperationOutcome -- same shape as every other FHIR read-one
 *   route in this codebase.
 */
class SpecimenFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Specimen";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Values of the seeded available specimen. */
    private const IDENTIFIER = "test-fixture-SPEC-1";
    private const ACCESSION = "test-fixture-ACC-1";
    private const TYPE_CODE = "122555007";
    private const TYPE_DISPLAY = "Venous blood specimen";
    private const METHOD_CODE = "28520004";
    private const METHOD_DISPLAY = "Venipuncture";
    private const LOCATION_CODE = "368208006";
    private const LOCATION_DISPLAY = "Left upper arm structure";
    private const COLLECTED_DATE = "2024-04-02 09:30:00";
    private const CONDITION_CODE = "ACT";
    private const CONDITION_DISPLAY = "Actual";
    private const COMMENTS = "test-fixture comment";

    /** Identifiers of the other seeded specimens. */
    private const DELETED_IDENTIFIER = "test-fixture-SPEC-2";
    private const OTHER_PATIENT_IDENTIFIER = "test-fixture-SPEC-3";

    private ApiTestClient $testClient;
    private ProcedureOrderFixtureManager $orderFixtureManager;

    /** @var list<string> uuid_registry entries (binary) created by the seed helpers */
    private array $registeredUuids = [];

    /** @var list<int> procedure_specimen rows inserted by seedSpecimens() */
    private array $specimenIds = [];

    /** procedure_order row inserted for the second patient */
    private ?int $otherOrderId = null;

    /** pid of the second patient */
    private ?int $otherPid = null;

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->orderFixtureManager = new ProcedureOrderFixtureManager();
    }

    /**
     * Drop the specimens, the second patient and its order, the uuids registered for them,
     * then everything the order fixture manager created (order, codes, forms row, lab,
     * practitioner, encounter, patient).
     */
    protected function tearDown(): void
    {
        foreach ($this->specimenIds as $specimenId) {
            QueryUtils::sqlStatementThrowException("DELETE FROM procedure_specimen WHERE procedure_specimen_id = ?", [$specimenId]);
        }
        $this->specimenIds = [];
        if ($this->otherOrderId !== null) {
            QueryUtils::sqlStatementThrowException("DELETE FROM procedure_order WHERE procedure_order_id = ?", [$this->otherOrderId]);
            $this->otherOrderId = null;
        }
        if ($this->otherPid !== null) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->otherPid]);
            $this->otherPid = null;
        }
        foreach ($this->registeredUuids as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid = ?", [$uuid]);
        }
        $this->registeredUuids = [];
        $this->orderFixtureManager->removeFixtures();

        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------------

    /**
     * Searching by patient returns that patient's available specimen: not the deleted one,
     * and not another patient's.
     */
    public function testSearchByPatientReturnsOnlyAvailableSpecimensOfThatPatient(): void
    {
        $seed = $this->seedSpecimens();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $seed['patient']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->resourceIds($this->assertSpecimenBundle($this->decodeJsonArray($result)));
        $this->assertNotContains($seed['other_patient'], $ids, "Another patient's specimen should not be returned");
        $this->assertNotContains($seed['deleted'], $ids, "A deleted specimen should not be returned without a status filter");
        $this->assertSame([$seed['available']], $ids);
    }

    /**
     * Each token and date search parameter, scoped to the fixture patient, returns exactly the
     * expected specimens.
     *
     * @param array<string, string> $params
     * @param list<string> $expected keys of the seed array
     */
    #[DataProvider('searchProvider')]
    public function testSearchParameters(array $params, array $expected): void
    {
        $seed = $this->seedSpecimens();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $seed['patient']] + $params);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->resourceIds($this->assertSpecimenBundle($this->decodeJsonArray($result)));
        $this->assertEqualsCanonicalizing(array_map(static fn(string $key): string => $seed[$key], $expected), $ids);
    }

    /**
     * @return array<string, array{array<string, string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function searchProvider(): array
    {
        return [
            'identifier' => [['identifier' => self::IDENTIFIER], ['available']],
            'identifier of another specimen' => [['identifier' => 'test-fixture-none'], []],
            'accession' => [['accession' => self::ACCESSION], ['available']],
            'type' => [['type' => self::TYPE_CODE], ['available']],
            'collected on or after' => [['collected' => 'ge2024-04-01'], ['available']],
            'collected before' => [['collected' => 'lt2024-04-01'], []],
            'status available' => [['status' => 'available'], ['available']],
            'status entered-in-error' => [['status' => 'entered-in-error'], ['deleted']],
            // a deleted specimen reads back as entered-in-error, so nothing stored is unavailable
            'status unavailable' => [['status' => 'unavailable'], []],
            'status unknown' => [['status' => 'test-fixture-none'], []],
        ];
    }

    /**
     * Searching by _id returns exactly that specimen.
     */
    public function testSearchByIdReturnsOneSpecimen(): void
    {
        $seed = $this->seedSpecimens();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $seed['available']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->resourceIds($this->assertSpecimenBundle($this->decodeJsonArray($result)));
        $this->assertSame([$seed['available']], $ids);
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the available specimen maps every seeded column to its FHIR element.
     */
    public function testGetOneReturnsSpecimen(): void
    {
        $seed = $this->seedSpecimens();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['available']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Specimen", $resource['resourceType'] ?? null);
        $this->assertSame($seed['available'], $resource['id'] ?? null);
        $this->assertSame("available", $resource['status'] ?? null);
        $this->assertReference("Patient/" . $seed['patient'], $resource, 'subject');

        $identifier = $this->firstElement($resource, 'identifier');
        $this->assertSame(self::IDENTIFIER, $identifier['value'] ?? null);

        $this->assertArrayHasKey('accessionIdentifier', $resource);
        $this->assertIsArray($resource['accessionIdentifier']);
        $this->assertSame(self::ACCESSION, $resource['accessionIdentifier']['value'] ?? null);

        $this->assertArrayHasKey('type', $resource);
        $this->assertIsArray($resource['type']);
        $this->assertCoding(self::TYPE_CODE, self::TYPE_DISPLAY, "http://snomed.info/sct", $this->firstElement($resource['type'], 'coding'));

        $this->assertArrayHasKey('collection', $resource);
        $collection = $resource['collection'];
        $this->assertIsArray($collection);
        $collected = $collection['collectedDateTime'] ?? null;
        $this->assertIsString($collected);
        $this->assertStringStartsWith('2024-04-02T', $collected);
        $this->assertArrayHasKey('method', $collection);
        $this->assertIsArray($collection['method']);
        $this->assertCoding(self::METHOD_CODE, self::METHOD_DISPLAY, "http://snomed.info/sct", $this->firstElement($collection['method'], 'coding'));
        $this->assertArrayHasKey('bodySite', $collection);
        $this->assertIsArray($collection['bodySite']);
        $this->assertCoding(self::LOCATION_CODE, self::LOCATION_DISPLAY, "http://snomed.info/sct", $this->firstElement($collection['bodySite'], 'coding'));

        $container = $this->firstElement($resource, 'container');
        $this->assertArrayHasKey('capacity', $container);
        $this->assertIsArray($container['capacity']);
        $this->assertEquals(5, $container['capacity']['value'] ?? null);
        $this->assertSame("mL", $container['capacity']['unit'] ?? null);

        $condition = $this->firstElement($resource, 'condition');
        $this->assertCoding(
            self::CONDITION_CODE,
            self::CONDITION_DISPLAY,
            "http://terminology.hl7.org/CodeSystem/v2-0493",
            $this->firstElement($condition, 'coding')
        );

        $note = $this->firstElement($resource, 'note');
        $this->assertSame(self::COMMENTS, $note['text'] ?? null);
    }

    /**
     * Reading a deleted specimen by its uuid returns 404: the read goes through the same search,
     * which leaves deleted specimens out unless `status` asks for them.
     */
    public function testGetOneDeletedSpecimen(): void
    {
        $seed = $this->seedSpecimens();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['deleted']);
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
     * Seed one procedure order (with its patient) holding an available and a deleted specimen,
     * and a second patient with an order and one specimen, and return the uuids the assertions
     * need.
     *
     * @return array{available: string, deleted: string, other_patient: string, patient: string}
     */
    private function seedSpecimens(): array
    {
        $this->assertSame(1, $this->orderFixtureManager->installFixtures(), "The fixture file holds one order");
        $orderIds = $this->orderFixtureManager->getInstalledOrderIds();
        $this->assertCount(1, $orderIds);
        $orderId = $orderIds[0];

        $patientUuid = QueryUtils::fetchSingleValue(
            "SELECT pd.uuid FROM procedure_order po JOIN patient_data pd ON pd.pid = po.patient_id WHERE po.procedure_order_id = ?",
            'uuid',
            [$orderId]
        );
        $this->assertIsString($patientUuid, "The seeded order should join to its patient");

        $available = $this->insertSpecimen($orderId, [
            'specimen_identifier' => self::IDENTIFIER,
            'accession_identifier' => self::ACCESSION,
            'specimen_type_code' => self::TYPE_CODE,
            'specimen_type' => self::TYPE_DISPLAY,
            'collection_method_code' => self::METHOD_CODE,
            'collection_method' => self::METHOD_DISPLAY,
            'specimen_location_code' => self::LOCATION_CODE,
            'specimen_location' => self::LOCATION_DISPLAY,
            'collected_date' => self::COLLECTED_DATE,
            'volume_value' => '5.000',
            'volume_unit' => 'mL',
            'condition_code' => self::CONDITION_CODE,
            'specimen_condition' => self::CONDITION_DISPLAY,
            'comments' => self::COMMENTS,
            'deleted' => 0,
        ]);
        $deleted = $this->insertSpecimen($orderId, [
            'specimen_identifier' => self::DELETED_IDENTIFIER,
            'collected_date' => self::COLLECTED_DATE,
            'deleted' => 1,
        ]);

        $nextPid = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($nextPid);
        $this->otherPid = (int) $nextPid;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname) VALUES (?, ?, ?, ?)",
            [$this->otherPid, $this->registerUuid(['table_name' => 'patient_data']), self::OTHER_PATIENT_IDENTIFIER, self::OTHER_PATIENT_IDENTIFIER]
        );
        $this->otherOrderId = QueryUtils::sqlInsert("INSERT INTO procedure_order (patient_id) VALUES (?)", [$this->otherPid]);
        $otherPatient = $this->insertSpecimen($this->otherOrderId, [
            'specimen_identifier' => self::OTHER_PATIENT_IDENTIFIER,
            'accession_identifier' => self::ACCESSION,
            'specimen_type_code' => self::TYPE_CODE,
            'collected_date' => self::COLLECTED_DATE,
            'deleted' => 0,
        ]);

        return [
            'available' => $available,
            'deleted' => $deleted,
            'other_patient' => $otherPatient,
            'patient' => UuidRegistry::uuidToString($patientUuid),
        ];
    }

    /**
     * Insert a procedure_specimen row on sequence 1 of the given order.
     *
     * @param array<string, string|int> $columns column => value
     * @return string The specimen's uuid as a string
     */
    private function insertSpecimen(int $orderId, array $columns): string
    {
        $uuid = $this->registerUuid(['table_name' => 'procedure_specimen', 'table_id' => 'procedure_specimen_id']);
        $columns = ['uuid' => $uuid, 'procedure_order_id' => $orderId, 'procedure_order_seq' => 1] + $columns;
        $assignments = implode(', ', array_map(static fn(string $column): string => "`$column` = ?", array_keys($columns)));
        $this->specimenIds[] = QueryUtils::sqlInsert("INSERT INTO procedure_specimen SET $assignments", array_values($columns));
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Create a uuid in uuid_registry for the given table and remember it for tearDown.
     *
     * @param array<string, string> $associations UuidRegistry constructor associations
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
     * Assert the shape of a Specimen search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertSpecimenBundle(array $bundle): array
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
            $this->assertSame("Specimen", $entry['resource']['resourceType'] ?? null);
            $resources[] = $entry['resource'];
        }
        return $resources;
    }

    /**
     * @param list<array<array-key, mixed>> $resources
     * @return list<mixed>
     */
    private function resourceIds(array $resources): array
    {
        return array_map(static fn(array $resource): mixed => $resource['id'] ?? null, $resources);
    }

    /**
     * Return the first element of a list-valued element, asserting it is there.
     *
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     */
    private function firstElement(array $resource, string $element): array
    {
        $this->assertArrayHasKey($element, $resource);
        $this->assertIsArray($resource[$element]);
        $this->assertArrayHasKey(0, $resource[$element], "'$element' should not be empty");
        $this->assertIsArray($resource[$element][0]);
        return $resource[$element][0];
    }

    /**
     * Assert a Coding's code, display and system.
     *
     * @param array<array-key, mixed> $coding
     */
    private function assertCoding(string $code, string $display, string $system, array $coding): void
    {
        $this->assertSame($code, $coding['code'] ?? null);
        $this->assertSame($display, $coding['display'] ?? null);
        $this->assertSame($system, $coding['system'] ?? null);
    }

    /**
     * Assert a Reference's target on $holder's $element.
     *
     * @param array<array-key, mixed> $holder
     */
    private function assertReference(string $expected, array $holder, string $element): void
    {
        $this->assertArrayHasKey($element, $holder);
        $this->assertIsArray($holder[$element]);
        $this->assertSame($expected, $holder[$element]['reference'] ?? null);
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
