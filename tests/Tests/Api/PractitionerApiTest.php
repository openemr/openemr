<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * Practitioner API Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PractitionerApiTest extends TestCase
{
    const PRACTITIONER_API_ENDPOINT = "/apis/default/api/practitioner";

    private ApiTestClient $testClient;
    private PractitionerFixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $practitionerRecord;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
            $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new PractitionerFixtureManager();
        /** @var array<string, mixed> $fixture */
        $fixture = $this->fixtureManager->getSinglePractitionerFixture();
        $this->practitionerRecord = $fixture;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testInvalidPost(): void
    {
        unset($this->practitionerRecord["fname"]);
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testPost(): void
    {
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);

        $this->assertEquals(201, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newPractitionerId = $responseBody["data"]["id"];
        $this->assertIsInt($newPractitionerId);
        $this->assertGreaterThan(0, $newPractitionerId);

        $newPractitionerUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newPractitionerUuid);
    }

    public function testInvalidPut(): void
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
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testPut(): void
    {
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);

        $practitionerUuid = $responseBody["data"]["uuid"];
        assert(is_string($practitionerUuid));

        $this->practitionerRecord["email"] = "help@pennfirm.com";
        $actualResponse = $this->testClient->put(self::PRACTITIONER_API_ENDPOINT, $practitionerUuid, $this->practitionerRecord);

        $this->assertEquals(200, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $updatedResource = $responseBody["data"];

        $this->assertEquals($this->practitionerRecord["email"], $updatedResource["email"]);
    }

    public function testGetOneInvalidId(): void
    {
        $actualResponse = $this->testClient->getOne(self::PRACTITIONER_API_ENDPOINT, "not-a-uuid");
        $this->assertEquals(400, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    public function testGetOne(): void
    {
        $actualResponse = $this->testClient->post(self::PRACTITIONER_API_ENDPOINT, $this->practitionerRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $practitionerUuid = $responseBody["data"]["uuid"];
        assert(is_string($practitionerUuid));
        $practitionerId = $responseBody["data"]["id"];

        $actualResponse = $this->testClient->getOne(self::PRACTITIONER_API_ENDPOINT, $practitionerUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals($practitionerUuid, $responseBody["data"]["uuid"]);
        $this->assertEquals($practitionerId, $responseBody["data"]["id"]);
    }


    public function testGetAll(): void
    {
        $this->fixtureManager->installPractitionerFixtures();

        $actualResponse = $this->testClient->get(self::PRACTITIONER_API_ENDPOINT, ["npi" => "0123456789"]);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $searchResults = $responseBody["data"];
        $this->assertGreaterThan(1, $searchResults);

        foreach ($searchResults as $searchResult) {
            $this->assertEquals("0123456789", $searchResult["npi"]);
        }
    }
}
