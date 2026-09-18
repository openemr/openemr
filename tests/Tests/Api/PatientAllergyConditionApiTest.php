<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

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
    private const PATIENT_API_ENDPOINT = "/apis/default/api/patient";

    /** @var array<string, mixed> */
    private array $patientRecord;
    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        /** @var array<string, mixed> $record */
        $record = $this->fixtureManager->getSinglePatientFixture();
        $this->patientRecord = $record;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeAllergyIntoleranceFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * Decode a JSON response body into an associative array.
     *
     * @return array<string, mixed>
     */
    private function decodeBody(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded);
        /** @var array<string, mixed> $decoded */
        return $decoded;
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
        $body = $this->decodeBody($response);
        $this->assertIsArray($body["data"]);
        $this->assertIsString($body["data"]["uuid"]);
        return $body["data"]["uuid"];
    }

    /**
     * Helper to create an allergy for a patient via the per-patient endpoint.
     *
     * @param array<string, string> $allergyData
     * @return array<string, mixed>
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
        return $this->decodeBody($response);
    }

    /**
     * Helper to create a medical problem for a patient.
     *
     * @param array<string, string> $conditionData
     * @return array<string, mixed>
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
        return $this->decodeBody($response);
    }

    /**
     * Perform a GET request and decode the JSON response body.
     *
     * @return array{response: ResponseInterface, body: array<string, mixed>}
     */
    private function getAndDecode(string $url): array
    {
        $response = $this->testClient->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        return ['response' => $response, 'body' => $this->decodeBody($response)];
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
        $patientUuid = $this->createPatient();

        $allergyData = [
            "title" => "Penicillin",
            "type" => "allergy",
            "reaction" => "hives",
            "verification" => "confirmed",
            "begdate" => "2020-01-15",
        ];
        $this->createAllergy($patientUuid, $allergyData);

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $result = $this->getAndDecode($url);
        $body = $result['body'];

        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertGreaterThan(
            0,
            count($body["data"]),
            "Per-patient allergy endpoint should return at least 1 allergy "
            . "but returned empty. This was the original bug where UUID was "
            . "passed as lists.pid (numeric field)."
        );

        $found = false;
        foreach ($body["data"] as $allergy) {
            $this->assertIsArray($allergy);
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

        $this->assertIsArray($createBody["data"]);
        $allergyUuid = $createBody["data"]["uuid"] ?? null;
        if ($allergyUuid === null) {
            $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
            $result = $this->getAndDecode($url);
            $listBody = $result['body'];
            $this->assertNotEmpty($listBody["data"], "No allergies found for patient");
            $this->assertIsArray($listBody["data"]);
            $this->assertIsArray($listBody["data"][0]);
            $allergyUuid = $listBody["data"][0]["uuid"];
        }

        $this->assertIsString($allergyUuid);

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid
            . "/allergy/" . $allergyUuid;
        $result = $this->getAndDecode($url);
        $body = $result['body'];

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

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $result = $this->getAndDecode($url);
        $body = $result['body'];

        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertGreaterThan(
            0,
            count($body["data"]),
            "Per-patient medical_problem endpoint should return at least 1 "
            . "condition but returned empty. This was the original bug where "
            . "ConditionService foreach overwrote the TokenSearchField."
        );
    }

    /**
     * Test that GET /api/patient/:puuid/medical_problem/:muuid returns a single
     * condition by its UUID within the patient context.
     */
    public function testGetSingleConditionByPatient(): void
    {
        $patientUuid = $this->createPatient();

        $conditionData = [
            "title" => "Essential Hypertension",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:I10",
            "begdate" => "2021-03-20",
        ];
        $createBody = $this->createCondition($patientUuid, $conditionData);

        $this->assertIsArray($createBody["data"]);
        $conditionUuid = $createBody["data"]["uuid"] ?? null;
        if ($conditionUuid === null) {
            $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
            $result = $this->getAndDecode($url);
            $listBody = $result['body'];
            $this->assertNotEmpty($listBody["data"], "No conditions found for patient");
            $this->assertIsArray($listBody["data"]);
            $this->assertIsArray($listBody["data"][0]);
            $conditionUuid = $listBody["data"][0]["uuid"];
        }

        $this->assertIsString($conditionUuid);

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid
            . "/medical_problem/" . $conditionUuid;
        $result = $this->getAndDecode($url);
        $body = $result['body'];

        $this->assertArrayHasKey("data", $body);
        $this->assertNotEmpty(
            $body["data"],
            "Single condition endpoint returned empty for valid condition UUID"
        );
    }

    /**
     * Test that per-patient allergy endpoint does NOT return allergies
     * belonging to a different patient.
     */
    public function testAllergyIsolationBetweenPatients(): void
    {
        $patientUuid1 = $this->createPatient();

        $patient2Record = $this->patientRecord;
        $patient2Record["fname"] = "TestIsolation";
        $patient2Record["lname"] = "Patient2";
        $response2 = $this->testClient->post(
            self::PATIENT_API_ENDPOINT,
            $patient2Record
        );
        $this->assertEquals(201, $response2->getStatusCode());
        $body2 = $this->decodeBody($response2);
        $this->assertIsArray($body2["data"]);
        $this->assertIsString($body2["data"]["uuid"]);
        $patientUuid2 = $body2["data"]["uuid"];

        $allergyData = [
            "title" => "Codeine",
            "type" => "allergy",
            "reaction" => "nausea",
            "verification" => "confirmed",
            "begdate" => "2022-01-01",
        ];
        $this->createAllergy($patientUuid1, $allergyData);

        $url1 = self::PATIENT_API_ENDPOINT . "/" . $patientUuid1 . "/allergy";
        $result1 = $this->getAndDecode($url1);
        $body1 = $result1['body'];
        $this->assertIsArray($body1["data"]);
        $this->assertGreaterThan(0, count($body1["data"]),
            "Patient 1 should have at least 1 allergy");

        $url2 = self::PATIENT_API_ENDPOINT . "/" . $patientUuid2 . "/allergy";
        $result2 = $this->getAndDecode($url2);
        $body2 = $result2['body'];

        $found = false;
        $this->assertIsArray($body2["data"]);
        foreach ($body2["data"] as $allergy) {
            $this->assertIsArray($allergy);
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
