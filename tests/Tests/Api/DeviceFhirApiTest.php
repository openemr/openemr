<?php

/**
 * FHIR Device API tests.
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
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR Device read endpoints:
 *   GET /fhir/Device           (search  -> returns a Bundle)
 *   GET /fhir/Device/:uuid     (read one -> returns a single Device)
 *
 * Modeled on AllergyIntoleranceFhirApiTest / ConditionFhirApiTest. POST/PUT coverage
 * lives in DeviceFhirWriteApiTest; this file seeds via POST only so GET can read a
 * real resource, then cleans up medical_device list rows in tearDown.
 *
 * These tests PIN CURRENT SERVER BEHAVIOR, not the FHIR spec:
 * - Search bundles use type "collection" (FhirResourcesService), not "searchset".
 * - getOne on a well-formed but nonexistent uuid returns 404 with an empty JSON
 *   array; a malformed uuid returns 400 with validationErrors -- not an
 *   OperationOutcome (RestControllerHelper::handleFhirProcessingResult).
 */
class DeviceFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/Device';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;

    /** @var array<string, mixed> */
    private array $fhirFixture;

    /** FHIR uuid of the patient that owns the seeded device. */
    private string $patientUuid;

    /**
     * Authenticate, install a fixture patient, and bind the Device JSON to that patient.
     */
    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/device.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        unset($fixture['id']);
        $stringKeyedFixture = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyedFixture[$key] = $value;
        }
        $this->fhirFixture = $stringKeyedFixture;
    }

    /**
     * Remove seeded medical-device rows, fixture patients, and the OAuth client.
     */
    protected function tearDown(): void
    {
        if (isset($this->fixtureManager)) {
            $this->fixtureManager->removeDeviceFixtures();
            $this->fixtureManager->removePatientFixtures();
        }
        try {
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    // ---------------------------------------------------------------------
    // Happy path: search
    // ---------------------------------------------------------------------

    /**
     * Searching by a patient with no devices should return an empty Bundle
     * with HTTP 200 -- NOT a 404.
     */
    public function testSearchByPatientReturnsEmptyWhenNoDevice(): void
    {
        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid]);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals('Bundle', $bundle['resourceType'] ?? null);
        $this->assertEquals('collection', $bundle['type'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null);
        $this->assertEmpty($bundle['entry'] ?? []);
    }

    /**
     * Searching by patient should return a FHIR Bundle (type "collection")
     * that includes the seeded device.
     */
    public function testSearchByPatientReturnsSeededDevice(): void
    {
        $uuid = $this->createDevice();

        $result = $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid]);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $body = $result->getBody()->getContents();
        $this->assertNotEmpty($body, 'Device search should return a body');
        $bundle = json_decode((string) $body, true);
        $this->assertIsArray($bundle);
        $this->assertEquals('Bundle', $bundle['resourceType'] ?? null);
        $this->assertEquals('collection', $bundle['type'] ?? null);
        $this->assertArrayHasKey('entry', $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertNotEmpty($bundle['entry'], 'Search should return at least the seeded device');

        $found = false;
        foreach ($bundle['entry'] as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey('resource', $entry);
            $this->assertIsArray($entry['resource']);
            $this->assertEquals('Device', $entry['resource']['resourceType'] ?? null);
            if (($entry['resource']['id'] ?? null) === $uuid) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'Search by patient should include the seeded device');
    }

    /**
     * Searching by the seeded device's _id should return exactly that one
     * Device, and it should reference the seeded patient.
     */
    public function testSearchByIdReturnsSeededDevice(): void
    {
        $uuid = $this->createDevice();

        $result = $this->testClient->get(self::ENDPOINT, ['_id' => $uuid]);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertArrayHasKey('entry', $bundle);
        $this->assertIsArray($bundle['entry']);
        $this->assertCount(1, $bundle['entry'], 'Search by _id should return a single device');

        $this->assertIsArray($bundle['entry'][0]);
        $this->assertArrayHasKey('resource', $bundle['entry'][0]);
        $resource = $bundle['entry'][0]['resource'];
        $this->assertIsArray($resource);
        $this->assertEquals($uuid, $resource['id']);
        $this->assertCanonicalPatient($resource);
    }

    /**
     * A search that matches no device should return an empty Bundle with
     * HTTP 200 -- NOT a 404.
     */
    public function testSearchNoMatchReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(
            self::ENDPOINT,
            ['_id' => '00000000-0000-0000-0000-000000000000']
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals('Bundle', $bundle['resourceType'] ?? null);
        $this->assertEquals('collection', $bundle['type'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null, 'No-match search should have total 0');
        $this->assertEmpty($bundle['entry'] ?? [], 'No-match search should have no entries');
    }

    // ---------------------------------------------------------------------
    // Happy path: read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded device by uuid should return exactly that Device,
     * and it should reference the seeded patient.
     */
    public function testReadByUuidReturnsSeededDevice(): void
    {
        $uuid = $this->createDevice();

        $result = $this->testClient->get(self::ENDPOINT . '/' . $uuid);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $resource = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($resource);
        $this->assertEquals('Device', $resource['resourceType'] ?? null);
        $this->assertEquals($uuid, $resource['id'] ?? null);
        $this->assertCanonicalPatient($resource);
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
            self::ENDPOINT . '/' . $this->fixtureManager->getUnregisteredUuid()
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $raw = trim((string) $result->getBody()->getContents());
        $this->assertNotSame('', $raw, '404 should return a JSON body');
        $this->assertSame(
            '[',
            $raw[0],
            '404 body must be a JSON array [], not an object {} or an OperationOutcome'
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
        $result = $this->testClient->get(self::ENDPOINT . '/not-a-valid-uuid');
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('validationErrors', $body);
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
            getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost',
            false
        );
        $result = $unauthClient->get(self::ENDPOINT);
        $this->assertOAuthUnauthorizedResponse($result);
    }

    /**
     * Read-one without a valid token should be rejected with 401 and an OAuth denial body.
     */
    public function testReadUnauthorizedReturns401(): void
    {
        $uuid = $this->createDevice();
        $unauthClient = new ApiTestClient(
            getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost',
            false
        );
        $result = $unauthClient->get(self::ENDPOINT . '/' . $uuid);
        $this->assertOAuthUnauthorizedResponse($result);
    }

    /**
     * POST the fixture Device and return its uuid so GET tests can read it.
     */
    private function createDevice(): string
    {
        $result = $this->testClient->post(self::ENDPOINT, $this->fhirFixture);
        $body = (string) $result->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $result->getStatusCode(),
            'Seeding Device should return 201. Body: ' . $body
        );
        $row = json_decode($body, true);
        $this->assertIsArray($row);
        $this->assertIsString($row['uuid'] ?? null, 'Seeding Device should return its uuid');
        return $row['uuid'];
    }

    /**
     * Device.patient must be the canonical FHIR R4 shape "Patient/<uuid>".
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertCanonicalPatient(array $resource): void
    {
        $this->assertArrayHasKey('patient', $resource);
        $this->assertIsArray($resource['patient']);
        $this->assertArrayHasKey('reference', $resource['patient']);
        $this->assertSame(
            'Patient/' . $this->patientUuid,
            $resource['patient']['reference'],
            'Device.patient must be the canonical FHIR reference Patient/{uuid}'
        );
    }

    /**
     * 401 must be an OAuth denial body (error + "denied the request"), not a FHIR resource.
     */
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
}
