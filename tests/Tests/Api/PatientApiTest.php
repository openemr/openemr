<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * Patient API Endpoint Test Cases.
 * NOTE: currently disabled (by naming convention) until work is completed to support running as part of Travis CI
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class PatientApiTest extends TestCase
{
    const PATIENT_API_ENDPOINT = "/apis/default/api/patient";
    private $testClient;
    private $fixtureManager;

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
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @covers ::post with an invalid patient request
     */
    public function testInvalidPost()
    {
        unset($this->patientRecord["fname"]);
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    /**
     * @covers ::post with a valid patient request
     */
    public function testPost()
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newPatientPid = $responseBody["data"]["pid"];
        $this->assertIsInt($newPatientPid);
        $this->assertGreaterThan(0, $newPatientPid);

        $newPatientUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newPatientUuid);
    }

    /**
     * @covers ::put with an invalid pid and uuid
     */
    public function testInvalidPut()
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $this->patientRecord["phone_home"] = "222-222-2222";
        $actualResponse = $this->testClient->put(self::PATIENT_API_ENDPOINT, "not-a-uuid", $this->patientRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    /**
     * @covers ::put with a valid resource id and payload
     */
    public function testPut()
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);

        $patientUuid = $responseBody["data"]["uuid"];

        $this->patientRecord["phone_home"] = "222-222-2222";
        $actualResponse = $this->testClient->put(self::PATIENT_API_ENDPOINT, $patientUuid, $this->patientRecord);

        $this->assertEquals(200, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $updatedResource = $responseBody["data"];
        $this->assertEquals($this->patientRecord["phone_home"], $updatedResource["phone_home"]);
    }

    /**
     * @covers ::getOne with an invalid pid
     */
    public function testGetOneInvalidPid()
    {
        $actualResponse = $this->testClient->getOne(self::PATIENT_API_ENDPOINT, "not-a-uuid");
        $this->assertEquals(400, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    /**
     * @covers ::getOne with a valid pid
     */
    public function testGetOne()
    {
        $actualResponse = $this->testClient->post(self::PATIENT_API_ENDPOINT, $this->patientRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $patientUuid = $responseBody["data"]["uuid"];
        $patientPid = $responseBody["data"]["pid"];

        $actualResponse = $this->testClient->getOne(self::PATIENT_API_ENDPOINT, $patientUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals($patientUuid, $responseBody["data"]["uuid"]);
        $this->assertEquals($patientPid, $responseBody["data"]["pid"]);
    }


    /**
     * @covers ::getAll
     */
    public function testGetAll()
    {
        $this->fixtureManager->installPatientFixtures();

        $actualResponse = $this->testClient->get(self::PATIENT_API_ENDPOINT, array("postal_code" => "90210"));
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $searchResults = $responseBody["data"];
        $this->assertGreaterThan(1, $searchResults);

        foreach ($searchResults as $index => $searchResult) {
            $this->assertEquals("90210", $searchResult["postal_code"]);
        }
    }
}
