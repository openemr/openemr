<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Facility API Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class FacilityApiTest extends TestCase
{
    const FACILITY_API_ENDPOINT = "/apis/default/api/facility";
    private ApiTestClient $testClient;
    /** @var array<string, mixed> */
    private array $facilityRecord;
    private FacilityFixtureManager $fixtureManager;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FacilityFixtureManager();
        /** @var array<string, mixed> $fixture */
        $fixture = $this->fixtureManager->getSingleFacilityFixture();
        $this->facilityRecord = $fixture;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
//        $this->testClient->cleanupRevokeAuth();
//        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testInvalidPost(): void
    {
        unset($this->facilityRecord["name"]);
        $actualResponse = $this->testClient->post(self::FACILITY_API_ENDPOINT, $this->facilityRecord);

        $this->assertEquals(400, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    #[Test]
    public function testPost(): void
    {
        $actualResponse = $this->testClient->post(self::FACILITY_API_ENDPOINT, $this->facilityRecord);

        $this->assertEquals(Response::HTTP_CREATED, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $newFacilityId = $responseBody["data"]["id"];
        $this->assertIsInt($newFacilityId);
        $this->assertGreaterThan(0, $newFacilityId);

        $newFacilityUuid = $responseBody["data"]["uuid"];
        $this->assertIsString($newFacilityUuid);
    }

    #[Test]
    public function testInvalidPut(): void
    {
        $actualResponse = $this->testClient->post(self::FACILITY_API_ENDPOINT, $this->facilityRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        $this->facilityRecord["email"] = "help@pennfirm.com";
        $actualResponse = $this->testClient->put(
            self::FACILITY_API_ENDPOINT,
            "not-a-uuid",
            $this->facilityRecord
        );

        $this->assertEquals(400, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    #[Test]
    public function testPut(): void
    {
        $actualResponse = $this->testClient->post(self::FACILITY_API_ENDPOINT, $this->facilityRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);

        $facilityUuid = $responseBody["data"]["uuid"];
        assert(is_string($facilityUuid));

        $this->facilityRecord["email"] = "help@pennfirm.com";
        $actualResponse = $this->testClient->put(self::FACILITY_API_ENDPOINT, $facilityUuid, $this->facilityRecord);

        $this->assertEquals(200, $actualResponse->getStatusCode());
        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $updatedResource = $responseBody["data"];
        assert(is_array($updatedResource));
        $this->assertEquals($this->facilityRecord["email"], $updatedResource["email"]);
    }

    #[Test]
    public function testGetOneInvalidId(): void
    {
        $actualResponse = $this->testClient->getOne(self::FACILITY_API_ENDPOINT, "not-a-uuid");
        $this->assertEquals(400, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(1, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals(0, count($responseBody["data"]));
    }

    #[Test]
    public function testGetOne(): void
    {
        $actualResponse = $this->testClient->post(self::FACILITY_API_ENDPOINT, $this->facilityRecord);
        $this->assertEquals(201, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $facilityUuid = $responseBody["data"]["uuid"];
        assert(is_string($facilityUuid));
        $facilityId = $responseBody["data"]["id"];

        $actualResponse = $this->testClient->getOne(self::FACILITY_API_ENDPOINT, $facilityUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));
        $this->assertEquals($facilityUuid, $responseBody["data"]["uuid"]);
        $this->assertEquals($facilityId, $responseBody["data"]["id"]);
    }

    #[Test]
    public function testGetAll(): void
    {
        $this->fixtureManager->installFacilityFixtures();

        $actualResponse = $this->testClient->get(self::FACILITY_API_ENDPOINT, ["facility_npi" => "0123456789"]);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody["validationErrors"]));
        $this->assertEquals(0, count($responseBody["internalErrors"]));

        $searchResults = $responseBody["data"];
        $this->assertGreaterThan(1, $searchResults);

        foreach ($searchResults as $searchResult) {
            $this->assertEquals("0123456789", $searchResult["facility_npi"]);
        }
    }
}
