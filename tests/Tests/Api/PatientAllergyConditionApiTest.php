<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * Per-Patient Allergy and Condition API Endpoint Test Cases.
 *
 * Tests that per-patient endpoints return correct results when filtering
 * by patient UUID. These tests specifically cover the bug fix where:
 * - GET /api/patient/:puuid/allergy was passing UUID to numeric lists.pid field
 * - GET /api/patient/:puuid/medical_problem had ConditionService foreach
 *   overwriting the TokenSearchField for puuid
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    studebaker8
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PatientAllergyConditionApiTest extends TestCase
{
    const PATIENT_API_ENDPOINT = "/apis/default/api/patient";
    const ALLERGY_API_ENDPOINT = "/apis/default/api/allergy";
    const CONDITION_API_ENDPOINT = "/apis/default/api/medical_problem";

    private $testClient;
    private $fixtureManager;
    private $patientRecord;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->patientRecord = (array) $this->fixtureManager->getSinglePatientFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->fixtureManager->removeAllergyIntoleranceFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * Helper to create a patient and return its UUID.
     */
    private function createPatient(): string
    {
        $response = $this->testClient->post(
            self::PATIENT_API_ENDPOINT,
            $this->patientRecord
        );
        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body["data"]["uuid"]);
        return $body["data"]["uuid"];
    }

    /**
     * Helper to create an allergy for a patient via the per-patient endpoint.
     */
    private function createAllergy(string $patientUuid, array $allergyData): array
    {
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $response = $this->testClient->post($url, $allergyData);
        $this->assertContains(
            $response->getStatusCode(),
            [200, 201],
            "Failed to create allergy: " . (string) $response->getBody()
        );
        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Helper to create a medical problem for a patient.
     */
    private function createCondition(string $patientUuid, array $conditionData): array
    {
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $response = $this->testClient->post($url, $conditionData);
        $this->assertContains(
            $response->getStatusCode(),
            [200, 201],
            "Failed to create condition: " . (string) $response->getBody()
        );
        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Test that GET /api/patient/:puuid/allergy returns allergies
     * belonging to that specific patient.
     *
     * Previously this endpoint always returned empty results because
     * the route passed the UUID string as lists.pid (a numeric field).
     * Fix: changed to pass as 'puuid' which the service handles correctly.
     */
    public function testGetAllergiesByPatient(): void
    {
        // Create a patient
        $patientUuid = $this->createPatient();

        // Create an allergy for this patient
        $allergyData = [
            "title" => "Penicillin",
            "type" => "allergy",
            "reaction" => "hives",
            "verification" => "confirmed",
            "begdate" => "2020-01-15",
        ];
        $this->createAllergy($patientUuid, $allergyData);

        // GET per-patient allergies
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $response = $this->testClient->get($url);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey("data", $body);
        $this->assertGreaterThan(
            0,
            count($body["data"]),
            "Per-patient allergy endpoint should return at least 1 allergy "
            . "but returned empty. This was the original bug where UUID was "
            . "passed as lists.pid (numeric field)."
        );

        // Verify the allergy data
        $found = false;
        foreach ($body["data"] as $allergy) {
            if ($allergy["title"] === "Penicillin") {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Created allergy 'Penicillin' not found in per-patient results");
    }

    /**
     * Test that GET /api/patient/:puuid/allergy/:auuid returns a single
     * allergy by its UUID within the patient context.
     */
    public function testGetSingleAllergyByPatient(): void
    {
        $patientUuid = $this->createPatient();

        $allergyData = [
            "title" => "Sulfa Drugs",
            "type" => "allergy",
            "reaction" => "rash",
            "verification" => "confirmed",
            "begdate" => "2019-06-01",
        ];
        $createBody = $this->createAllergy($patientUuid, $allergyData);

        // Get the allergy UUID from the creation response
        $allergyUuid = $createBody["data"]["uuid"] ?? null;
        if ($allergyUuid === null) {
            // If UUID not in create response, fetch from per-patient list
            $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
            $listResponse = $this->testClient->get($url);
            $listBody = json_decode((string) $listResponse->getBody(), true);
            $this->assertNotEmpty($listBody["data"], "No allergies found for patient");
            $allergyUuid = $listBody["data"][0]["uuid"];
        }

        $this->assertNotEmpty($allergyUuid, "Could not obtain allergy UUID");

        // GET single allergy by UUID within patient context
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid
            . "/allergy/" . $allergyUuid;
        $response = $this->testClient->get($url);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey("data", $body);
        $this->assertNotEmpty(
            $body["data"],
            "Single allergy endpoint returned empty for valid allergy UUID"
        );
    }

    /**
     * Test that GET /api/patient/:puuid/medical_problem returns conditions
     * belonging to that specific patient.
     *
     * Previously this endpoint always returned empty results because
     * ConditionService::getAll() created a TokenSearchField for puuid
     * but the foreach loop overwrote it with a StringSearchField.
     */
    public function testGetConditionsByPatient(): void
    {
        $patientUuid = $this->createPatient();

        $conditionData = [
            "title" => "Essential Hypertension",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:I10",
            "begdate" => "2021-03-20",
        ];
        $this->createCondition($patientUuid, $conditionData);

        // GET per-patient conditions
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $response = $this->testClient->get($url);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey("data", $body);
        $this->assertGreaterThan(
            0,
            count($body["data"]),
            "Per-patient medical_problem endpoint should return at least 1 "
            . "condition but returned empty. This was the original bug where "
            . "ConditionService foreach overwrote the TokenSearchField."
        );
    }

    /**
     * Test that per-patient allergy endpoint does NOT return allergies
     * belonging to a different patient.
     */
    public function testAllergyIsolationBetweenPatients(): void
    {
        // Create two patients
        $patientUuid1 = $this->createPatient();

        // Need a different patient record for the second patient
        $patient2Record = $this->patientRecord;
        $patient2Record["fname"] = "TestIsolation";
        $patient2Record["lname"] = "Patient2";
        $response2 = $this->testClient->post(
            self::PATIENT_API_ENDPOINT,
            $patient2Record
        );
        $this->assertEquals(201, $response2->getStatusCode());
        $body2 = json_decode((string) $response2->getBody(), true);
        $patientUuid2 = $body2["data"]["uuid"];

        // Create allergy ONLY for patient 1
        $allergyData = [
            "title" => "Codeine",
            "type" => "allergy",
            "reaction" => "nausea",
            "verification" => "confirmed",
            "begdate" => "2022-01-01",
        ];
        $this->createAllergy($patientUuid1, $allergyData);

        // Verify patient 1 HAS the allergy
        $url1 = self::PATIENT_API_ENDPOINT . "/" . $patientUuid1 . "/allergy";
        $response1 = $this->testClient->get($url1);
        $body1 = json_decode((string) $response1->getBody(), true);
        $this->assertGreaterThan(0, count($body1["data"]),
            "Patient 1 should have at least 1 allergy");

        // Verify patient 2 does NOT have the allergy
        $url2 = self::PATIENT_API_ENDPOINT . "/" . $patientUuid2 . "/allergy";
        $response2 = $this->testClient->get($url2);
        $body2 = json_decode((string) $response2->getBody(), true);

        $found = false;
        foreach ($body2["data"] as $allergy) {
            if ($allergy["title"] === "Codeine") {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found,
            "Patient 2 should NOT have Patient 1's allergy (Codeine). "
            . "If this fails, per-patient filtering is broken.");
    }
}
