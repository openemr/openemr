<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;

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

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private int $testPatientPid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();

        // Create a patient via API for prescriptions
        $patientFixture = (array) $this->fixtureManager->getSinglePatientFixture();
        $response = $this->testClient->post(self::PATIENT_API_ENDPOINT, $patientFixture);
        $this->assertEquals(201, $response->getStatusCode(), "Failed to create test patient");
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->testPatientPid = $responseBody["data"]["pid"];
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPost(): void
    {
        $prescriptionData = [
            'patient_id' => $this->testPatientPid,
            'drug' => 'Test Medication',
            'dosage' => '100mg',
            'quantity' => '30',
            'provider_id' => 1,
        ];

        $actualResponse = $this->testClient->post(self::PRESCRIPTION_API_ENDPOINT, $prescriptionData);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newPrescriptionId = $responseBody["data"]["id"];
        $this->assertIsInt($newPrescriptionId);
        $this->assertGreaterThan(0, $newPrescriptionId);

        $newPrescriptionUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newPrescriptionUuid);
    }

    public function testDelete(): void
    {
        // First create a prescription to delete
        $prescriptionData = [
            'patient_id' => $this->testPatientPid,
            'drug' => 'Medication To Delete',
            'dosage' => '50mg',
            'quantity' => '10',
            'provider_id' => 1,
        ];

        $createResponse = $this->testClient->post(self::PRESCRIPTION_API_ENDPOINT, $prescriptionData);
        $this->assertEquals(201, $createResponse->getStatusCode());

        $createBody = json_decode((string) $createResponse->getBody(), true);
        $prescriptionId = $createBody["data"]["id"];

        // Soft-delete it (sets active = 0)
        $deleteResponse = $this->testClient->delete(self::PRESCRIPTION_API_ENDPOINT, $prescriptionId);

        $this->assertEquals(200, $deleteResponse->getStatusCode());
        $deleteBody = json_decode((string) $deleteResponse->getBody(), true);
        $this->assertEquals(0, count($deleteBody["validationErrors"]));
        $this->assertEquals(0, count($deleteBody["internalErrors"]));
        $this->assertEquals('record deleted', $deleteBody["data"]["message"]);
    }

    public function testDeleteNonExistent(): void
    {
        // Try to soft-delete a prescription that doesn't exist.
        // Returns 200 because the UPDATE succeeds (affects 0 rows).
        $deleteResponse = $this->testClient->delete(self::PRESCRIPTION_API_ENDPOINT, 999999999);

        $this->assertEquals(200, $deleteResponse->getStatusCode());
    }
}
