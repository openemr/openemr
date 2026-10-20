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

    /**
     * POST /api/patient/:puuid/allergy — creates an allergy and returns
     * a 201 with `data.uuid` populated. Locks the create response shape
     * (data.id + data.uuid) that AllergyIntoleranceService::insert produces.
     */
    public function testPostAllergyCreatesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $allergyData = [
            "title" => "Latex",
            "type" => "allergy",
            "reaction" => "contact dermatitis",
            "verification" => "confirmed",
            "begdate" => "2023-05-10",
        ];
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $response = $this->testClient->post($url, $allergyData);

        $this->assertEquals(201, $response->getStatusCode(),
            "POST should return 201. Body: " . (string) $response->getBody());
        $body = $this->decodeBody($response);
        $this->assertSame([], $body["validationErrors"] ?? ["SENTINEL"],
            "Create must produce no validation errors. Body: " . (string) $response->getBody());
        $this->assertIsArray($body["data"]);
        $this->assertArrayHasKey("uuid", $body["data"]);
        $this->assertIsString($body["data"]["uuid"]);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $body["data"]["uuid"]
        );
    }

    /**
     * PUT /api/patient/:puuid/allergy/:auuid — updates an existing allergy
     * and the follow-up GET reflects the new value. AllergyIntoleranceService::update
     * resolves the owning patient via `pid` before writing (puuid is a read alias
     * that buildUpdateColumns() drops), so the test does not need to pass puuid
     * in the body.
     */
    public function testPutAllergyUpdatesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $allergyData = [
            "title" => "Peanuts",
            "type" => "allergy",
            "reaction" => "anaphylaxis",
            "verification" => "confirmed",
            "begdate" => "2018-03-15",
        ];
        $createBody = $this->createAllergy($patientUuid, $allergyData);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $allergyUuid = $createBody["data"]["uuid"];

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $putResponse = $this->testClient->put($url, $allergyUuid, [
            "title" => "Peanuts (severe)",
            "begdate" => "2018-03-15",
            "diagnosis" => "ICD10:T78.00XA",
        ]);
        $this->assertEquals(200, $putResponse->getStatusCode(),
            "PUT should return 200. Body: " . (string) $putResponse->getBody());

        $follow = $this->testClient->get($url . "/" . $allergyUuid);
        $this->assertEquals(200, $follow->getStatusCode());
        $followBody = $this->decodeBody($follow);
        $this->assertIsArray($followBody["data"]);
        // Per-patient single-GET returns `data` as a list of rows
        // (isMultipleResultResponse=true in the controller). Narrow before
        // reading the title.
        $this->assertNotEmpty($followBody["data"], "Follow-up GET must return the row");
        $this->assertIsArray($followBody["data"][0]);
        $this->assertSame("Peanuts (severe)", $followBody["data"][0]["title"] ?? null);
    }

    /**
     * DELETE /api/patient/:puuid/allergy/:auuid — hard-deletes the row from
     * `lists` (AllergyIntoleranceService::delete runs a `DELETE FROM lists
     * WHERE pid = ? AND uuid = ? AND type = 'allergy'`). After the delete,
     * the single-GET returns an empty-data response (handleProcessingResult
     * flattens zero records to `data: []`), locking that shape too.
     */
    public function testDeleteAllergyRemovesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $allergyData = [
            "title" => "Aspirin",
            "type" => "allergy",
            "reaction" => "hives",
            "verification" => "confirmed",
            "begdate" => "2020-07-04",
        ];
        $createBody = $this->createAllergy($patientUuid, $allergyData);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $allergyUuid = $createBody["data"]["uuid"];

        $baseUrl = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/allergy";
        $deleteResponse = $this->testClient->delete($baseUrl, $allergyUuid);
        $this->assertEquals(200, $deleteResponse->getStatusCode(),
            "DELETE should return 200. Body: " . (string) $deleteResponse->getBody());

        $follow = $this->testClient->getOne($baseUrl, $allergyUuid);
        $followBody = $this->decodeBody($follow);
        $this->assertIsArray($followBody["data"]);
        $this->assertSame([], $followBody["data"],
            "After DELETE, single-GET on the same uuid must return empty data.");
    }

    /**
     * POST /api/patient/:puuid/medical_problem — creates a condition and
     * returns 201 with `data.uuid`. ConditionService::insert mirrors
     * AllergyIntoleranceService::insert's shape.
     */
    public function testPostMedicalProblemCreatesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $conditionData = [
            "title" => "Type 2 Diabetes",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:E11.9",
            "begdate" => "2017-11-02",
        ];
        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $response = $this->testClient->post($url, $conditionData);

        $this->assertEquals(201, $response->getStatusCode(),
            "POST should return 201. Body: " . (string) $response->getBody());
        $body = $this->decodeBody($response);
        $this->assertSame([], $body["validationErrors"] ?? ["SENTINEL"],
            "Create must produce no validation errors. Body: " . (string) $response->getBody());
        $this->assertIsArray($body["data"]);
        $this->assertArrayHasKey("uuid", $body["data"]);
        $this->assertIsString($body["data"]["uuid"]);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $body["data"]["uuid"]
        );
    }

    /**
     * PUT /api/patient/:puuid/medical_problem/:muuid — updates an existing
     * condition and the follow-up GET reflects the new value.
     */
    public function testPutMedicalProblemUpdatesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $conditionData = [
            "title" => "Essential Hypertension",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:I10",
            "begdate" => "2019-02-14",
        ];
        $createBody = $this->createCondition($patientUuid, $conditionData);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $conditionUuid = $createBody["data"]["uuid"];

        $url = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $putResponse = $this->testClient->put($url, $conditionUuid, [
            "title" => "Essential Hypertension (resolved)",
            "begdate" => "2019-02-14",
            "enddate" => "2024-08-01",
            "diagnosis" => "ICD10:I10",
        ]);
        $this->assertEquals(200, $putResponse->getStatusCode(),
            "PUT should return 200. Body: " . (string) $putResponse->getBody());

        $follow = $this->testClient->get($url . "/" . $conditionUuid);
        $this->assertEquals(200, $follow->getStatusCode());
        $followBody = $this->decodeBody($follow);
        $this->assertIsArray($followBody["data"]);
        // Per-patient single-GET returns `data` as a list of rows
        // (isMultipleResultResponse=true in the controller).
        $this->assertNotEmpty($followBody["data"], "Follow-up GET must return the row");
        $this->assertIsArray($followBody["data"][0]);
        $this->assertSame("Essential Hypertension (resolved)", $followBody["data"][0]["title"] ?? null);
    }

    /**
     * DELETE /api/patient/:puuid/medical_problem/:muuid — hard-deletes the
     * row from `lists`. ConditionService::delete runs a parameterised
     * `DELETE FROM lists WHERE pid = ? AND uuid = ? AND type = 'medical_problem'`.
     */
    public function testDeleteMedicalProblemRemovesRecord(): void
    {
        $patientUuid = $this->createPatient();

        $conditionData = [
            "title" => "Asthma",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:J45.909",
            "begdate" => "2015-08-20",
        ];
        $createBody = $this->createCondition($patientUuid, $conditionData);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $conditionUuid = $createBody["data"]["uuid"];

        $baseUrl = self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/medical_problem";
        $deleteResponse = $this->testClient->delete($baseUrl, $conditionUuid);
        $this->assertEquals(200, $deleteResponse->getStatusCode(),
            "DELETE should return 200. Body: " . (string) $deleteResponse->getBody());

        $follow = $this->testClient->getOne($baseUrl, $conditionUuid);
        $followBody = $this->decodeBody($follow);
        $this->assertIsArray($followBody["data"]);
        $this->assertSame([], $followBody["data"],
            "After DELETE, single-GET on the same uuid must return empty data.");
    }

    /**
     * GET /api/allergy — top-level list (no patient scope). After seeding
     * an allergy through the per-patient POST, the top-level list must
     * surface it.
     */
    public function testGetAllergyTopLevelList(): void
    {
        $patientUuid = $this->createPatient();
        $createBody = $this->createAllergy($patientUuid, [
            "title" => "Shellfish",
            "type" => "allergy",
            "reaction" => "swelling",
            "verification" => "confirmed",
            "begdate" => "2021-06-18",
        ]);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $createdUuid = $createBody["data"]["uuid"];

        $response = $this->testClient->get("/apis/default/api/allergy");
        $this->assertEquals(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertGreaterThan(0, count($body["data"]),
            "Top-level GET /api/allergy must return at least the one we created.");

        $uuids = array_map(
            static fn($row): string => is_array($row) && is_string($row["uuid"] ?? null) ? (string) $row["uuid"] : '',
            $body["data"]
        );
        $this->assertContains($createdUuid, $uuids,
            "Top-level /api/allergy must include the just-created allergy.");
    }

    /**
     * GET /api/allergy/:auuid — top-level single lookup. Returns the
     * single-record data shape (data as the row object, not an array of rows).
     */
    public function testGetAllergyTopLevelSingle(): void
    {
        $patientUuid = $this->createPatient();
        $createBody = $this->createAllergy($patientUuid, [
            "title" => "Pollen",
            "type" => "allergy",
            "reaction" => "sneezing",
            "verification" => "confirmed",
            "begdate" => "2022-04-01",
        ]);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $createdUuid = $createBody["data"]["uuid"];

        $response = $this->testClient->get("/apis/default/api/allergy/" . $createdUuid);
        $this->assertEquals(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertSame($createdUuid, $body["data"]["uuid"] ?? null);
        $this->assertSame("Pollen", $body["data"]["title"] ?? null);
    }

    /**
     * GET /api/medical_problem — top-level list surfaces a condition
     * created via the per-patient POST.
     */
    public function testGetMedicalProblemTopLevelList(): void
    {
        $patientUuid = $this->createPatient();
        $createBody = $this->createCondition($patientUuid, [
            "title" => "Migraine",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:G43.909",
            "begdate" => "2016-09-01",
        ]);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $createdUuid = $createBody["data"]["uuid"];

        $response = $this->testClient->get("/apis/default/api/medical_problem");
        $this->assertEquals(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertGreaterThan(0, count($body["data"]),
            "Top-level GET /api/medical_problem must return at least the one we created.");

        $uuids = array_map(
            static fn($row): string => is_array($row) && is_string($row["uuid"] ?? null) ? (string) $row["uuid"] : '',
            $body["data"]
        );
        $this->assertContains($createdUuid, $uuids);
    }

    /**
     * GET /api/medical_problem/:muuid — top-level single lookup returns
     * the row as a single data object.
     */
    public function testGetMedicalProblemTopLevelSingle(): void
    {
        $patientUuid = $this->createPatient();
        $createBody = $this->createCondition($patientUuid, [
            "title" => "GERD",
            "type" => "medical_problem",
            "diagnosis" => "ICD10:K21.9",
            "begdate" => "2020-02-20",
        ]);
        $this->assertIsArray($createBody["data"]);
        $this->assertIsString($createBody["data"]["uuid"]);
        $createdUuid = $createBody["data"]["uuid"];

        $response = $this->testClient->get("/apis/default/api/medical_problem/" . $createdUuid);
        $this->assertEquals(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertSame($createdUuid, $body["data"]["uuid"] ?? null);
        $this->assertSame("GERD", $body["data"]["title"] ?? null);
    }

    /**
     * GET /api/allergy/:auuid — unknown UUID returns 404 with empty data.
     * AllergyIntoleranceRestController::getOne calls
     * handleProcessingResult(..., 404) when zero rows match.
     */
    public function testGetAllergyTopLevelSingleNotFound(): void
    {
        $response = $this->testClient->get(
            "/apis/default/api/allergy/00000000-0000-4000-8000-000000000000"
        );
        $this->assertEquals(404, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertSame([], $body["data"] ?? null,
            "Unknown-UUID allergy lookup must not return any row.");
    }

    /**
     * GET /api/medical_problem/:muuid — unknown UUID returns 404 with
     * empty data (same shape as the allergy variant).
     */
    public function testGetMedicalProblemTopLevelSingleNotFound(): void
    {
        $response = $this->testClient->get(
            "/apis/default/api/medical_problem/00000000-0000-4000-8000-000000000000"
        );
        $this->assertEquals(404, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertSame([], $body["data"] ?? null,
            "Unknown-UUID medical_problem lookup must not return any row.");
    }
}
