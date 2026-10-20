<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * Patient API Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PatientApiTest extends TestCase
{
    const PATIENT_API_ENDPOINT = "/apis/default/api/patient";
    private $testClient;
    private $fixtureManager;
    private $patientRecord;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->patientRecord = $this->fixtureManager->getSinglePatientFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testInvalidPost(): void
    {
        unset($this->patientRecord["fname"]);
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testPost(): void
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newPatientPid = $responseBody["data"]["pid"];
        $this->assertIsInt($newPatientPid);
        $this->assertGreaterThan(0, $newPatientPid);

        $newPatientUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newPatientUuid);
    }

    public function testInvalidPut(): void
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $this->patientRecord["phone_home"] = "222-222-2222";
        $actualResponse = $this->testClient->put(self::PATIENT_API_ENDPOINT, "not-a-uuid", $this->patientRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testPut(): void
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);

        $patientUuid = $responseBody["data"]["uuid"];

        $this->patientRecord["phone_home"] = "222-222-2222";
        $actualResponse = $this->testClient->put(self::PATIENT_API_ENDPOINT, $patientUuid, $this->patientRecord);

        $this->assertEquals(200, $actualResponse->getStatusCode());
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $updatedResource = $responseBody["data"];
        $this->assertEquals($this->patientRecord["phone_home"], $updatedResource["phone_home"]);
    }

    public function testGetOneInvalidPid(): void
    {
        $actualResponse = $this->testClient->getOne(self::PATIENT_API_ENDPOINT, "not-a-uuid");
        $this->assertEquals(400, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testGetOne(): void
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $patientUuid = $responseBody["data"]["uuid"];
        $patientPid = $responseBody["data"]["pid"];

        $actualResponse = $this->testClient->getOne(self::PATIENT_API_ENDPOINT, $patientUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals($patientUuid, $responseBody["data"]["uuid"]);
        $this->assertEquals($patientPid, $responseBody["data"]["pid"]);
    }

    public function testGetAll(): void
    {
        $this->fixtureManager->installPatientFixtures();

        $actualResponse = $this->testClient->get(self::PATIENT_API_ENDPOINT, ["postal_code" => "90210"]);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $searchResults = $responseBody["data"];
        $this->assertGreaterThan(1, $searchResults);

        foreach ($searchResults as $searchResult) {
            $this->assertEquals("90210", $searchResult["postal_code"]);
        }
    }

    public function testGetEmployer(): void
    {
        // POST creates a patient and lets us resolve a known-good puuid
        // for the employer route lookup. The employer endpoint returns
        // a standard ProcessingResult envelope — empty data array for a
        // newly-created patient with no employer record.
        /** @var ApiTestClient $client */
        $client = $this->testClient;
        /** @var array<string, mixed> $patientRecord */
        $patientRecord = $this->patientRecord;
        $postResponse = $client->post(self::PATIENT_API_ENDPOINT, $patientRecord);
        $this->assertEquals(201, $postResponse->getStatusCode());
        $postBody = json_decode((string) $postResponse->getBody(), true);
        $this->assertIsArray($postBody);
        $this->assertIsArray($postBody["data"] ?? null);
        $this->assertIsString($postBody["data"]["uuid"] ?? null);
        $patientUuid = (string) $postBody["data"]["uuid"];

        $employerResponse = $client->get(self::PATIENT_API_ENDPOINT . "/" . $patientUuid . "/employer");
        $this->assertEquals(200, $employerResponse->getStatusCode());

        $employerBody = json_decode((string) $employerResponse->getBody(), true);
        $this->assertIsArray($employerBody);
        $this->assertArrayHasKey("data", $employerBody);
        $this->assertArrayHasKey("validationErrors", $employerBody);
        $this->assertArrayHasKey("internalErrors", $employerBody);
        $this->assertSame([], $employerBody["validationErrors"]);
        $this->assertSame([], $employerBody["internalErrors"]);
        $this->assertIsArray($employerBody["data"]);
    }

    public function testGetEmployerWithInvalidUuidReturns400(): void
    {
        // The route guards on UuidRegistry::isValidStringUUID and
        // returns 400 with a validationErrors.uuid entry when the path
        // segment isn't a parseable UUID — distinct from the 404 shape
        // a missing-but-valid UUID would produce.
        /** @var ApiTestClient $client */
        $client = $this->testClient;
        $response = $client->get(self::PATIENT_API_ENDPOINT . "/not-a-uuid/employer");
        $this->assertEquals(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("validationErrors", $body);
        $this->assertIsArray($body["validationErrors"]);
        $this->assertArrayHasKey("uuid", $body["validationErrors"]);
    }
}
