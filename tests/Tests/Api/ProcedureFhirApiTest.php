<?php

/**
 * FHIR Procedure API tests.
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
 * Tests for the FHIR Procedure endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/Procedure        (search -> returns a Bundle)
 *   GET /fhir/Procedure/:uuid  (read one -> returns a single Procedure)
 *
 * There is no POST/PUT route. FhirProcedureService merges two read-only sources:
 * - procedure orders (FhirProcedureOEProcedureService over ProcedureService): one Procedure per
 *   `procedure_order` row, with the order's own uuid as id and a basedOn reference to the
 *   ServiceRequest of the same uuid. Orders whose codes are all typed `laboratory_test`
 *   (`procedure_order_code.procedure_order_title`) are left out: those are lab results, served
 *   as DiagnosticReport/Observation instead. An order without a report is "preparation" and
 *   takes its performed date from `date_ordered`.
 * - surgeries (FhirProcedureSurgeryService over SurgeryService): one Procedure per `lists` row
 *   of type `surgery`. Status comes from the end date: none is "in-progress", set is "completed".
 *
 * Each test that needs data seeds one order (through ProcedureOrderFixtureManager, which also
 * brings the patient, encounter, lab and ordering practitioner) and one surgery for the same
 * patient; tearDown removes both.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService::createBundle()), not FHIR's
 *   "searchset".
 * - A malformed `_id` on the search route is not a 400: FhirProcedureService::getAll() turns
 *   the SearchFieldException into validation messages, and FhirProcedureRestController::getAll()
 *   never checks them, so the request succeeds with an empty Bundle. The read-one route, which
 *   does check them, answers 400 for the same value.
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON array
 *   (404) for an unknown one, not OperationOutcome -- same shape as every other FHIR read-one
 *   route in this codebase.
 */
class ProcedureFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Procedure";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Values of the seeded surgery row. */
    private const SURGERY_TITLE = "test-fixture-Appendectomy";
    private const SURGERY_BEGDATE = "2024-03-10 00:00:00";
    private const SURGERY_DIAGNOSIS = "CPT4:44950";

    private ApiTestClient $testClient;
    private ProcedureOrderFixtureManager $orderFixtureManager;

    /** @var list<string> uuid_registry entries (binary) created by seedProcedures() */
    private array $registeredUuids = [];

    private ?int $surgeryId = null;

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->orderFixtureManager = new ProcedureOrderFixtureManager();
    }

    /**
     * Drop the surgery row, the uuids registered for it and for the order, then everything
     * the order fixture manager created (order, codes, forms row, lab, practitioner,
     * encounter, patient).
     */
    protected function tearDown(): void
    {
        if ($this->surgeryId !== null) {
            QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE id = ?", [$this->surgeryId]);
            $this->surgeryId = null;
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
     * Searching by patient returns both sources for that patient: the order and the surgery.
     */
    public function testSearchByPatientReturnsOrderAndSurgeryProcedures(): void
    {
        $seed = $this->seedProcedures();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $seed['patient']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertProcedureBundle($this->decodeJsonArray($result));
        $this->assertEqualsCanonicalizing([$seed['order'], $seed['surgery']], $this->resourceIds($resources));
    }

    /**
     * Searching by the order's _id returns exactly one Procedure, although the order has two
     * codes (two rows in the underlying join).
     */
    public function testSearchByIdReturnsOneProcedurePerOrder(): void
    {
        $seed = $this->seedProcedures();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $seed['order']]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertProcedureBundle($this->decodeJsonArray($result));
        $this->assertSame([$seed['order']], $this->resourceIds($resources));
    }

    /**
     * The date parameter matches the order's date_ordered (it has no report) and the
     * surgery's begdate.
     *
     * @param list<'order'|'surgery'> $expected
     */
    #[DataProvider('dateSearchProvider')]
    public function testSearchByDate(string $date, array $expected): void
    {
        $seed = $this->seedProcedures();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $seed['patient'], 'date' => $date]);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resources = $this->assertProcedureBundle($this->decodeJsonArray($result));
        $expectedIds = array_map(static fn(string $source): string => $seed[$source], $expected);
        $this->assertEqualsCanonicalizing($expectedIds, $this->resourceIds($resources));
    }

    /**
     * @return array<string, array{string, list<'order'|'surgery'>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateSearchProvider(): array
    {
        return [
            'order date_ordered' => ['2025-01-15', ['order']],
            'surgery begdate' => ['2024-03-10', ['surgery']],
            'on or after 2025' => ['ge2025-01-01', ['order']],
            'before both' => ['lt2024-01-01', []],
        ];
    }

    /**
     * An order whose codes are all laboratory tests is not a Procedure: the search returns the
     * surgery only and reading the order answers 404.
     */
    public function testLaboratoryTestOrderIsNotAProcedure(): void
    {
        $seed = $this->seedProcedures();
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_order_code SET procedure_order_title = 'laboratory_test' WHERE procedure_order_id = ?",
            [$seed['order_id']]
        );

        $search = $this->testClient->get(self::ENDPOINT, ['patient' => $seed['patient']]);
        $this->assertSame(Response::HTTP_OK, $search->getStatusCode());
        $resources = $this->assertProcedureBundle($this->decodeJsonArray($search));
        $this->assertSame([$seed['surgery']], $this->resourceIds($resources));

        $read = $this->testClient->getOne(self::ENDPOINT, $seed['order']);
        $this->assertSame(Response::HTTP_NOT_FOUND, $read->getStatusCode());
    }

    /**
     * A malformed _id on the search route does not surface as a 400: the controller never
     * checks the ProcessingResult's validity for this route, so the request succeeds with an
     * empty Bundle.
     */
    public function testSearchWithMalformedIdReturnsEmptyBundle(): void
    {
        $this->seedProcedures();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => 'not-a-uuid']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame([], $this->assertProcedureBundle($bundle));
        $this->assertSame(0, $bundle['total'] ?? null);
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the order returns the Procedure built from it: no report yet, so "preparation"
     * performed on date_ordered, with subject, encounter, performer, basedOn and reasonCode.
     */
    public function testGetOneReturnsOrderProcedure(): void
    {
        $seed = $this->seedProcedures();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['order']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Procedure", $resource['resourceType'] ?? null);
        $this->assertSame($seed['order'], $resource['id'] ?? null);
        $this->assertSame("preparation", $resource['status'] ?? null, "An order without a report is not performed yet");
        $this->assertReference("Patient/" . $seed['patient'], $resource, 'subject');
        $this->assertReference("Encounter/" . $seed['encounter'], $resource, 'encounter');
        $this->assertReference("ServiceRequest/" . $seed['order'], $this->firstElement($resource, 'basedOn'));
        $this->assertReference("Practitioner/" . $seed['practitioner'], $this->firstElement($resource, 'performer'), 'actor');
        $this->assertPerformedOn('2025-01-15', $resource);

        // The fixture codes carry no code type, so the code is the procedure name as text.
        // Which of the order's two codes names the Procedure depends on the join's row order.
        $this->assertArrayHasKey('code', $resource);
        $this->assertIsArray($resource['code']);
        $this->assertContains(
            $resource['code']['text'] ?? null,
            ['Comprehensive Metabolic Panel', 'Complete Blood Count']
        );

        $reasonCoding = $this->firstElement($this->firstElement($resource, 'reasonCode'), 'coding');
        $this->assertSame("http://hl7.org/fhir/sid/icd-10", $reasonCoding['system'] ?? null);
        $this->assertSame("E78.5", $reasonCoding['code'] ?? null);
    }

    /**
     * Reading the surgery returns the Procedure built from the lists row: no end date, so
     * "in-progress", performed on begdate, coded in CPT.
     */
    public function testGetOneReturnsSurgeryProcedure(): void
    {
        $seed = $this->seedProcedures();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['surgery']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Procedure", $resource['resourceType'] ?? null);
        $this->assertSame($seed['surgery'], $resource['id'] ?? null);
        $this->assertSame("in-progress", $resource['status'] ?? null, "A surgery without an end date is in progress");
        $this->assertReference("Patient/" . $seed['patient'], $resource, 'subject');
        $this->assertPerformedOn('2024-03-10', $resource);
        $this->assertArrayNotHasKey('basedOn', $resource, "A surgery is not based on an order");

        $this->assertArrayHasKey('code', $resource);
        $this->assertIsArray($resource['code']);
        $coding = $this->firstElement($resource['code'], 'coding');
        $this->assertSame("http://www.ama-assn.org/go/cpt", $coding['system'] ?? null);
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
     * Seed one procedure order (with its patient, encounter and ordering practitioner) and one
     * surgery for the same patient, and return the uuids the assertions need.
     *
     * The order gets its uuid here rather than from the server's lazy backfill, so that the
     * uuid_registry entry is known and tearDown can remove it.
     *
     * @return array{order_id: int, order: string, surgery: string, patient: string, encounter: string, practitioner: string}
     */
    private function seedProcedures(): array
    {
        $this->assertSame(1, $this->orderFixtureManager->installFixtures(), "The fixture file holds one order");
        $orderIds = $this->orderFixtureManager->getInstalledOrderIds();
        $this->assertCount(1, $orderIds);
        $orderId = $orderIds[0];

        $orderUuid = $this->registerUuid(['table_name' => 'procedure_order', 'table_id' => 'procedure_order_id']);
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_order SET uuid = ? WHERE procedure_order_id = ?",
            [$orderUuid, $orderId]
        );

        $rows = QueryUtils::fetchRecords(
            <<<'SQL'
            SELECT po.patient_id, pd.uuid AS patient_uuid, fe.uuid AS encounter_uuid, u.uuid AS practitioner_uuid
            FROM procedure_order po
            JOIN patient_data pd ON pd.pid = po.patient_id
            JOIN form_encounter fe ON fe.encounter = po.encounter_id
            JOIN users u ON u.id = po.provider_id
            WHERE po.procedure_order_id = ?
            SQL,
            [$orderId]
        );
        $this->assertCount(1, $rows, "The seeded order should join to its patient, encounter and practitioner");
        $order = $rows[0];

        $surgeryUuid = $this->registerUuid(['table_name' => 'lists']);
        $this->surgeryId = QueryUtils::sqlInsert(
            <<<'SQL'
            INSERT INTO lists
            SET uuid = ?, pid = ?, type = 'surgery', title = ?, begdate = ?, diagnosis = ?,
                activity = 1, user = 'admin', date = ?
            SQL,
            [$surgeryUuid, $order['patient_id'], self::SURGERY_TITLE, self::SURGERY_BEGDATE, self::SURGERY_DIAGNOSIS, self::SURGERY_BEGDATE]
        );

        return [
            'order_id' => $orderId,
            'order' => UuidRegistry::uuidToString($orderUuid),
            'surgery' => UuidRegistry::uuidToString($surgeryUuid),
            'patient' => $this->uuidString($order, 'patient_uuid'),
            'encounter' => $this->uuidString($order, 'encounter_uuid'),
            'practitioner' => $this->uuidString($order, 'practitioner_uuid'),
        ];
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
     * Read a binary uuid column from a database row as its string form.
     *
     * @param array<array-key, mixed> $row
     */
    private function uuidString(array $row, string $column): string
    {
        $value = $row[$column] ?? null;
        $this->assertIsString($value, "Seeded row should carry a uuid in '$column'");
        return UuidRegistry::uuidToString($value);
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
     * Assert the shape of a Procedure search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertProcedureBundle(array $bundle): array
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
            $this->assertSame("Procedure", $entry['resource']['resourceType'] ?? null);
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
     * Assert a Reference's target, either on $holder itself or on its $element.
     *
     * @param array<array-key, mixed> $holder
     */
    private function assertReference(string $expected, array $holder, ?string $element = null): void
    {
        $reference = $holder;
        if ($element !== null) {
            $this->assertArrayHasKey($element, $holder);
            $this->assertIsArray($holder[$element]);
            $reference = $holder[$element];
        }
        $this->assertSame($expected, $reference['reference'] ?? null);
    }

    /**
     * Assert performedDateTime falls on the given date. The value is the local date converted
     * to UTC, so only the date part is compared.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertPerformedOn(string $date, array $resource): void
    {
        $performed = $resource['performedDateTime'] ?? null;
        $this->assertIsString($performed);
        $this->assertStringStartsWith($date . 'T', $performed);
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
