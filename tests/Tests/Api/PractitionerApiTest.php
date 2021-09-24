<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * Practitioner API Endpoint Test Cases.
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class PractitionerApiTest extends TestCase
{
    const PRACTITIONER_API_ENDPOINT = "/apis/default/api/practitioner";

    /**
     * @var ApiTestClient
     */
    private $testClient;
    private $fixtureManager;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
            $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new PractitionerFixtureManager();
        $this->practitionerRecord = (array) $this->fixtureManager->getSinglePractitionerFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @covers ::post with an invalid practitioner request
     */
    public function testInvalidPost()
    {
        unset($this->practitionerRecord["fname"]);
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    /**
     * @covers ::post with a valid practitioner request
     */
    public function testPost()
    {
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newPractitionerId = $responseBody["data"]["id"];
        $this->assertIsInt($newPractitionerId);
        $this->assertGreaterThan(0, $newPractitionerId);

        $newPractitionerUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newPractitionerUuid);
    }

    /**
     * @covers ::put with an invalid pid and uuid
     */
    public function testInvalidPut()
    {
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $this->practitionerRecord["email"] = "help@pennfirm.com";
        $actualResponse = $this->testClient->put(
            self::PRACTITIONER_API_ENDPOINT,
            "not-a-uuid",
            $this->practitionerRecord
        );

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
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);

        $practitionerUuid = $responseBody["data"]["uuid"];

        $this->practitionerRecord["email"] = "help@pennfirm.com";
        $actualResponse = $this->testClient->put(self::PRACTITIONER_API_ENDPOINT, $practitionerUuid, $this->practitionerRecord);

        $this->assertEquals(200, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $updatedResource = $responseBody["data"];

        $this->assertEquals($this->practitionerRecord["email"], $updatedResource["email"]);
    }

    /**
     * @covers ::getOne with an invalid pid
     */
    public function testGetOneInvalidId()
    {
        $actualResponse = $this->testClient->getOne(self::PRACTITIONER_API_ENDPOINT, "not-a-uuid");
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
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $practitionerUuid = $responseBody["data"]["uuid"];
        $practitionerId = $responseBody["data"]["id"];

        $actualResponse = $this->testClient->getOne(self::PRACTITIONER_API_ENDPOINT, $practitionerUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals($practitionerUuid, $responseBody["data"]["uuid"]);
        $this->assertEquals($practitionerId, $responseBody["data"]["id"]);
    }


    /**
     * @covers ::getAll
     */
    public function testGetAll()
    {
        $this->fixtureManager->installPractitionerFixtures();

        $actualResponse = $this->testClient->get(self::PRACTITIONER_API_ENDPOINT, array("npi" => "0123456789"));
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $searchResults = $responseBody["data"];
        $this->assertGreaterThan(1, $searchResults);

        foreach ($searchResults as $index => $searchResult) {
            $this->assertEquals("0123456789", $searchResult["npi"]);
        }
    }
}
