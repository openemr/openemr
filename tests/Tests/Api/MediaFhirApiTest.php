<?php

/**
 * FHIR Media API tests.
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
 * Tests for the FHIR Media endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/Media        (search -> returns a Bundle)
 *   GET /fhir/Media/:uuid  (read one -> returns a single Media)
 *
 * There is no POST/PUT route. FhirMediaService reads `documents` rows through DocumentService:
 * only documents tied to a patient, and only image, DICOM and video mime types. The content
 * points at the document's Binary resource.
 *
 * Each test that needs data seeds two patients and these documents, without categories (a
 * document with no category is accessible to every user, Document::can_access()):
 * - a JPEG for the first patient (the Media the tests look for)
 * - a PDF for the first patient (not a Media mime type)
 * - a PNG for the second patient
 * - a JPEG tied to no patient
 * - a deleted JPEG for the first patient (returned, with status entered-in-error)
 * tearDown removes them.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - Search bundles use type "collection" (FhirResourcesService::createBundle()), not FHIR's
 *   "searchset".
 * - Read-one errors return validationErrors (400) for a malformed uuid or an empty JSON array
 *   (404) for an unknown one, not OperationOutcome -- same shape as every other FHIR read-one
 *   route in this codebase.
 */
class MediaFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Media";

    /** Search bundles are typed "collection" by FhirResourcesService::createBundle(). */
    private const BUNDLE_TYPE = "collection";

    /** Prefix of every seeded name, so leftovers are easy to spot. */
    private const TAG = "test-fixture-media";

    /** Values of the seeded JPEG. */
    private const JPEG_NAME = "test-fixture-media-xray.jpg";
    private const JPEG_DATE = "2024-05-06 10:00:00";

    private ApiTestClient $testClient;

    /** @var list<string> uuid_registry entries (binary) created by the seed helpers */
    private array $registeredUuids = [];

    /** @var list<int> documents rows inserted by seedDocuments() */
    private array $documentIds = [];

    /** @var list<int> patient_data pids inserted by seedDocuments() */
    private array $pids = [];

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    /**
     * Drop the seeded documents and patients, then the uuids registered for them.
     */
    protected function tearDown(): void
    {
        foreach ($this->documentIds as $documentId) {
            QueryUtils::sqlStatementThrowException("DELETE FROM documents WHERE id = ?", [$documentId]);
        }
        $this->documentIds = [];
        foreach ($this->pids as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->pids = [];
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
     * Search parameters return exactly the expected Media.
     *
     * @param array<string, string> $params search parameters; a value naming a seed key is replaced by its uuid
     * @param list<string> $expected keys of the seed array
     */
    #[DataProvider('searchProvider')]
    public function testSearchParameters(array $params, array $expected): void
    {
        $seed = $this->seedDocuments();

        $query = array_map(static fn(string $value): string => $seed[$value] ?? $value, $params);
        $result = $this->testClient->get(self::ENDPOINT, $query);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $ids = $this->resourceIds($this->assertMediaBundle($this->decodeJsonArray($result)));
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
            'patient: images only, not the PDF' => [['patient' => 'patient'], ['jpeg', 'deleted']],
            'patient of the PNG' => [['patient' => 'other_patient'], ['png']],
            '_id of the JPEG' => [['_id' => 'jpeg'], ['jpeg']],
            'patient:missing=true still leaves out documents without a patient' => [['patient:missing' => 'true'], []],
            '_id of the PDF' => [['_id' => 'pdf'], []],
            '_id of the document without a patient' => [['_id' => 'no_patient'], []],
            '_id of the deleted JPEG' => [['_id' => 'deleted'], ['deleted']],
            'content-type of the JPEG' => [['patient' => 'patient', 'content-type' => 'image/jpeg'], ['jpeg', 'deleted']],
            'content-type of another image type' => [['patient' => 'patient', 'content-type' => 'image/png'], []],
            'content-type of the PDF' => [['patient' => 'patient', 'content-type' => 'application/pdf'], []],
            'title' => [['patient' => 'patient', 'title' => self::JPEG_NAME], ['jpeg']],
        ];
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the JPEG returns a completed Media for the patient whose content points at the
     * document's Binary resource.
     */
    public function testGetOneReturnsMedia(): void
    {
        $seed = $this->seedDocuments();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['jpeg']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Media", $resource['resourceType'] ?? null);
        $this->assertSame($seed['jpeg'], $resource['id'] ?? null);
        $this->assertSame("completed", $resource['status'] ?? null);
        $this->assertArrayHasKey('subject', $resource);
        $this->assertIsArray($resource['subject']);
        $this->assertSame("Patient/" . $seed['patient'], $resource['subject']['reference'] ?? null);

        $this->assertArrayHasKey('content', $resource);
        $content = $resource['content'];
        $this->assertIsArray($content);
        $this->assertSame("image/jpeg", $content['contentType'] ?? null);
        $this->assertSame(self::JPEG_NAME, $content['title'] ?? null);
        $url = $content['url'] ?? null;
        $this->assertIsString($url);
        $this->assertStringEndsWith("/fhir/Binary/" . $seed['jpeg'], $url);
    }

    /**
     * A deleted document is still returned, with status entered-in-error rather than completed,
     * the same mapping FhirPatientDocumentReferenceService uses for DocumentReference.
     */
    public function testGetOneDeletedDocumentIsEnteredInError(): void
    {
        $seed = $this->seedDocuments();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed['deleted']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame($seed['deleted'], $resource['id'] ?? null);
        $this->assertSame("entered-in-error", $resource['status'] ?? null);
    }

    /**
     * Documents that are not Media are not readable through the Media route: 404.
     */
    #[DataProvider('notMediaProvider')]
    public function testGetOneNotMediaReturnsNotFound(string $key): void
    {
        $seed = $this->seedDocuments();

        $result = $this->testClient->getOne(self::ENDPOINT, $seed[$key]);
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notMediaProvider(): array
    {
        return [
            'PDF' => ['pdf'],
            'document without a patient' => ['no_patient'],
        ];
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
     * Seed two patients and the four documents described on the class, and return the uuids
     * the assertions need.
     *
     * @return array{patient: string, other_patient: string, jpeg: string, pdf: string, png: string, no_patient: string, deleted: string}
     */
    private function seedDocuments(): array
    {
        [$pid, $patient] = $this->insertPatient("One");
        [$otherPid, $otherPatient] = $this->insertPatient("Two");

        return [
            'patient' => $patient,
            'other_patient' => $otherPatient,
            'jpeg' => $this->insertDocument($pid, self::JPEG_NAME, "image/jpeg"),
            'pdf' => $this->insertDocument($pid, self::TAG . "-report.pdf", "application/pdf"),
            'png' => $this->insertDocument($otherPid, self::TAG . "-photo.png", "image/png"),
            'no_patient' => $this->insertDocument(0, self::TAG . "-system.jpg", "image/jpeg"),
            'deleted' => $this->insertDocument($pid, self::TAG . "-deleted.jpg", "image/jpeg", 1),
        ];
    }

    /**
     * Insert a patient.
     *
     * @return array{int, string} pid and the patient's uuid as a string
     */
    private function insertPatient(string $lname): array
    {
        $nextPid = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($nextPid);
        $pid = (int) $nextPid;
        $uuid = $this->registerUuid(['table_name' => 'patient_data']);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname) VALUES (?, ?, ?, ?)",
            [$pid, $uuid, self::TAG, $lname]
        );
        $this->pids[] = $pid;
        return [$pid, UuidRegistry::uuidToString($uuid)];
    }

    /**
     * Insert a document row (no file on disk: Media only reads the row).
     *
     * @param int $pid patient the document belongs to, 0 for none
     * @return string The document's uuid as a string
     */
    private function insertDocument(int $pid, string $name, string $mimetype, int $deleted = 0): string
    {
        $uuid = $this->registerUuid(['table_name' => 'documents']);
        // documents.id is not auto-increment: ids come from the sequences table, like Document does
        $id = QueryUtils::generateId();
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO documents
            SET id = ?, uuid = ?, type = 'file_url', url = ?, name = ?, mimetype = ?, foreign_id = ?,
                `date` = ?, revision = NOW(), deleted = ?
            SQL,
            [$id, $uuid, "file:///tmp/" . $name, $name, $mimetype, $pid, self::JPEG_DATE, $deleted]
        );
        $this->documentIds[] = $id;
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
     * Assert the shape of a Media search Bundle and return its resources.
     *
     * @param array<array-key, mixed> $bundle
     * @return list<array<array-key, mixed>>
     */
    private function assertMediaBundle(array $bundle): array
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
            $this->assertSame("Media", $entry['resource']['resourceType'] ?? null);
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
