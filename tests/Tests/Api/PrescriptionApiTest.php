<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Prescription API Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ivan Googla <ivan.jo.dev@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Ivan Googla
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PrescriptionApiTest extends TestCase
{
    private const PRESCRIPTION_API_ENDPOINT = "/apis/default/api/prescription";
    private const PATIENT_API_ENDPOINT = "/apis/default/api/patient";
    private const MEDICATION_LIST_ENDPOINT = "/apis/default/api/patient/%d/medication";
    private const FHIR_MEDICATION_REQUEST_ENDPOINT = "/apis/default/fhir/MedicationRequest";

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private int $testPatientPid;
    private string $testPatientUuid;
    private string $drugSuffix;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        // Prescriptions and list rows outlive removePatientFixtures(), and pids
        // are reused, so a fixed drug name would let one run see the previous
        // run's rows. Make every drug name unique to this test instance.
        $this->drugSuffix = ' ' . bin2hex(random_bytes(4));

        // Create a patient via API for prescriptions
        $patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $response = $this->testClient->post(self::PATIENT_API_ENDPOINT, $patientFixture);
        $this->assertEquals(201, $response->getStatusCode(), "Failed to create test patient");
        /** @var array{data: array{pid: int, uuid: string}} $responseBody */
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->testPatientPid = $responseBody["data"]["pid"];
        $this->testPatientUuid = $responseBody["data"]["uuid"];
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPost(): void
    {
        $prescriptionData = $this->buildPrescriptionData();

        $actualResponse = $this->testClient->post(self::PRESCRIPTION_API_ENDPOINT, $prescriptionData);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = $this->decodeResponse($actualResponse);
        $this->assertIsArray($responseBody["validationErrors"]);
        $this->assertCount(0, $responseBody["validationErrors"]);
        $this->assertIsArray($responseBody["internalErrors"]);
        $this->assertCount(0, $responseBody["internalErrors"]);

        $this->assertIsArray($responseBody["data"]);
        $this->assertIsInt($responseBody["data"]["id"]);
        $this->assertGreaterThan(0, $responseBody["data"]["id"]);
        $this->assertIsString($responseBody["data"]["uuid"]);
    }

    public function testGetOne(): void
    {
        $prescriptionData = $this->buildPrescriptionData('GetOne Test Drug');
        $created = $this->createPrescription($prescriptionData);

        $getResponse = $this->testClient->getOne(self::PRESCRIPTION_API_ENDPOINT, $created['uuid']);

        $this->assertEquals(200, $getResponse->getStatusCode());
        $responseBody = $this->decodeResponse($getResponse);
        // createProcessingResultResponse unwraps single results, so data is an object
        $record = $responseBody["data"];
        $this->assertIsArray($record);
        $this->assertEquals($created['uuid'], $record['uuid']);
        $this->assertEquals('GetOne Test Drug', $record['drug']);
        $this->assertEquals('100mg', $record['dosage']);
    }

    public function testGetOneNotFound(): void
    {
        // The service validates the UUID against the prescriptions table before querying.
        // An unknown UUID fails validation, returning 400 (not 404).
        $bogusUuid = '00000000-0000-0000-0000-000000000000';
        $getResponse = $this->testClient->getOne(self::PRESCRIPTION_API_ENDPOINT, $bogusUuid);

        $this->assertEquals(400, $getResponse->getStatusCode());
    }

    public function testGetAll(): void
    {
        $this->createPrescription($this->buildPrescriptionData('Drug Alpha'));
        $this->createPrescription($this->buildPrescriptionData('Drug Beta'));

        // The list endpoint REQUIRES a patient_uuid query parameter —
        // tenant-wide enumeration is not supported.
        $getResponse = $this->testClient->get(
            self::PRESCRIPTION_API_ENDPOINT,
            ['patient_uuid' => $this->testPatientUuid]
        );

        $this->assertEquals(200, $getResponse->getStatusCode());
        $responseBody = $this->decodeResponse($getResponse);
        $this->assertIsArray($responseBody["data"]);
        $this->assertGreaterThanOrEqual(2, count($responseBody["data"]));

        /** @var list<array<string, mixed>> $data */
        $data = $responseBody["data"];
        $drugs = array_column($data, 'drug');
        $this->assertContains('Drug Alpha', $drugs);
        $this->assertContains('Drug Beta', $drugs);
    }

    /**
     * `GET /api/prescription` without a patient_uuid must return 400 rather
     * than tenant-wide records.
     */
    public function testGetAllWithoutPatientUuidIsRejected(): void
    {
        $this->createPrescription($this->buildPrescriptionData('Should Not Return Without Bind'));

        $getResponse = $this->testClient->get(self::PRESCRIPTION_API_ENDPOINT);

        $this->assertEquals(400, $getResponse->getStatusCode());
        $body = $this->decodeResponse($getResponse);
        $this->assertIsArray($body["validationErrors"]);
        $this->assertArrayHasKey('patient.uuid', $body["validationErrors"]);
    }

    public function testRoundTrip(): void
    {
        // Create
        $prescriptionData = $this->buildPrescriptionData('Round Trip Drug', '250mg', '60');
        $created = $this->createPrescription($prescriptionData);

        // Read back and verify fields
        $getResponse = $this->testClient->getOne(self::PRESCRIPTION_API_ENDPOINT, $created['uuid']);
        $this->assertEquals(200, $getResponse->getStatusCode());
        $record = $this->decodeResponse($getResponse)["data"];
        $this->assertIsArray($record);
        $this->assertEquals('Round Trip Drug', $record['drug']);
        $this->assertEquals('250mg', $record['dosage']);
        $this->assertEquals('active', $record['status']);

        // Delete
        $deleteResponse = $this->testClient->delete(self::PRESCRIPTION_API_ENDPOINT, $created['uuid']);
        $this->assertEquals(200, $deleteResponse->getStatusCode());

        // Verify soft-deleted (status = stopped)
        $getAfterDelete = $this->testClient->getOne(self::PRESCRIPTION_API_ENDPOINT, $created['uuid']);
        $this->assertEquals(200, $getAfterDelete->getStatusCode());
        $deletedRecord = $this->decodeResponse($getAfterDelete)["data"];
        $this->assertIsArray($deletedRecord);
        $this->assertEquals('stopped', $deletedRecord['status']);
    }

    public function testDelete(): void
    {
        $created = $this->createPrescription($this->buildPrescriptionData('Medication To Delete'));

        $deleteResponse = $this->testClient->delete(self::PRESCRIPTION_API_ENDPOINT, $created['uuid']);

        $this->assertEquals(200, $deleteResponse->getStatusCode());
        $deleteBody = $this->decodeResponse($deleteResponse);
        $this->assertIsArray($deleteBody["validationErrors"]);
        $this->assertCount(0, $deleteBody["validationErrors"]);
        $this->assertIsArray($deleteBody["internalErrors"]);
        $this->assertCount(0, $deleteBody["internalErrors"]);
        /** @var array{message: string} $data */
        $data = $deleteBody["data"];
        $this->assertEquals('record deleted', $data["message"]);
    }

    public function testDeleteNonExistent(): void
    {
        // The service validates the UUID against the prescriptions table before deleting.
        // An unknown UUID fails validation, returning 400.
        $bogusUuid = '00000000-0000-0000-0000-000000000000';
        $deleteResponse = $this->testClient->delete(self::PRESCRIPTION_API_ENDPOINT, $bogusUuid);

        $this->assertEquals(400, $deleteResponse->getStatusCode());
    }

    public function testPostMissingRequiredFields(): void
    {
        $response = $this->testClient->post(self::PRESCRIPTION_API_ENDPOINT, [
            'dosage' => '100mg',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = $this->decodeResponse($response);
        $this->assertIsArray($body["validationErrors"]);
        $this->assertNotEmpty($body["validationErrors"]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, uuid: string}
     */
    /**
     * `prescriptions.medication` means "this drug also belongs on the patient's
     * medication list". The UI honours that by inserting the `lists` row and
     * linking it. The API accepted the field, wrote the column, and did none of
     * that work, so the row claimed a list entry that did not exist.
     */
    public function testPostWithMedicationFlagAddsToMedicationList(): void
    {
        $data = $this->buildPrescriptionData($this->drugName('Medication List Drug'));
        $data['medication'] = 1;
        $this->createPrescription($data);

        $entries = $this->medicationListEntries();
        $titles = array_column($entries, 'title');
        $this->assertContains($this->drugName('Medication List Drug'), $titles, 'drug should appear on the medication list');

        $entry = null;
        foreach ($entries as $candidate) {
            if (($candidate['title'] ?? null) === $this->drugName('Medication List Drug')) {
                $entry = $candidate;
                break;
            }
        }
        $this->assertIsArray($entry);
        // The list row is read back through the service layer, which needs a
        // uuid. A row inserted without one fails on read until the
        // missing-uuid backfill happens to run.
        $this->assertIsString($entry['uuid'] ?? null);
        $this->assertNotEmpty($entry['uuid']);
    }

    /**
     * FHIR MedicationRequest reads `prescriptions UNION lists`, and the `lists`
     * half excludes rows that carry `lists_medication.prescription_id`. Writing
     * the list row WITHOUT that link returns the same drug twice, which is a
     * worse outcome than the missing list entry it would fix.
     */
    public function testPostWithMedicationFlagDoesNotDuplicateMedicationRequest(): void
    {
        $data = $this->buildPrescriptionData($this->drugName('No Duplicate Drug'));
        $data['medication'] = 1;
        $this->createPrescription($data);

        $response = $this->testClient->get(
            self::FHIR_MEDICATION_REQUEST_ENDPOINT,
            ['patient' => $this->testPatientUuid]
        );
        $this->assertEquals(200, $response->getStatusCode());
        /** @var array{entry?: array<int, array{resource?: array<string, mixed>}>} $bundle */
        $bundle = json_decode((string) $response->getBody(), true);

        $matches = 0;
        foreach ($bundle['entry'] ?? [] as $bundleEntry) {
            $concept = $bundleEntry['resource']['medicationCodeableConcept'] ?? [];
            if (is_array($concept) && ($concept['text'] ?? null) === $this->drugName('No Duplicate Drug')) {
                $matches++;
            }
        }
        $this->assertSame(1, $matches, 'one prescription must not produce two MedicationRequest resources');
    }

    public function testPostWithoutMedicationFlagLeavesItOffTheMedicationList(): void
    {
        $data = $this->buildPrescriptionData($this->drugName('Prescription Only Drug'));
        $data['medication'] = 0;
        $this->createPrescription($data);

        $titles = array_column($this->medicationListEntries(), 'title');
        $this->assertNotContains($this->drugName('Prescription Only Drug'), $titles);
    }

    private function drugName(string $base): string
    {
        return $base . $this->drugSuffix;
    }

    /**
     * The medication list endpoint answers 404 rather than an empty array when
     * the patient has no medications, so treat anything but 200 as empty.
     *
     * @return list<array<mixed>>
     */
    private function medicationListEntries(): array
    {
        $response = $this->testClient->get(sprintf(self::MEDICATION_LIST_ENDPOINT, $this->testPatientPid));
        if ($response->getStatusCode() !== 200) {
            return [];
        }
        $decoded = json_decode((string) $response->getBody(), true);
        if (!is_array($decoded)) {
            return [];
        }

        $entries = [];
        foreach ($decoded as $row) {
            if (is_array($row)) {
                $entries[] = $row;
            }
        }

        return $entries;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array{id: int, uuid: string}
     */
    private function createPrescription(array $data): array
    {
        $response = $this->testClient->post(self::PRESCRIPTION_API_ENDPOINT, $data);
        $this->assertEquals(201, $response->getStatusCode(), "Failed to create test prescription");
        $body = $this->decodeResponse($response);
        /** @var array{id: int, uuid: string} $result */
        $result = $body["data"];
        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $json = (string) $response->getBody();
        $body = json_decode($json, true);
        $this->assertIsArray($body);
        /** @var array<string, mixed> $body */
        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPrescriptionData(
        string $drug = 'Test Medication',
        string $dosage = '100mg',
        string $quantity = '30',
    ): array {
        return [
            'patient_id' => $this->testPatientPid,
            'drug' => $drug,
            'dosage' => $dosage,
            'quantity' => $quantity,
            'provider_id' => 1,
        ];
    }
}
