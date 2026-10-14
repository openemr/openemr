<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\DrugFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Drug API Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class DrugApiTest extends TestCase
{
    const DRUG_API_ENDPOINT = '/apis/default/api/drug';
    private ApiTestClient $testClient;
    private DrugFixtureManager $fixtureManager;
    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new DrugFixtureManager();
        $this->fixtureManager->installFixtures();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    #[Test]
    public function testGetAll(): void
    {
        $actualResponse = $this->testClient->get(self::DRUG_API_ENDPOINT, []);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody['validationErrors']));
        $this->assertEquals(0, count($responseBody['internalErrors']));

        $drugNames = array_column($responseBody['data'], 'name');
        $this->assertContains('test-fixture-Ibuprofen', $drugNames);
        $this->assertContains('test-fixture-Acetaminophen', $drugNames);
    }

    #[Test]
    public function testGetOne(): void
    {
        $drugFixture = $this->fixtureManager->getSingleDrugFixture();
        $drugUuid = $this->fixtureManager->getInstalledDrugUuid($drugFixture['name']);

        $actualResponse = $this->testClient->getOne(self::DRUG_API_ENDPOINT, $drugUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEquals(0, count($responseBody['validationErrors']));
        $this->assertEquals(0, count($responseBody['internalErrors']));
        $this->assertEquals($drugFixture['name'], $responseBody['data']['name']);
    }

    #[Test]
    public function testGetOneNotFound(): void
    {
        // Known bug: DrugRestController uses the legacy RestControllerHelper::handleProcessingResult(),
        // which sets the status via the native http_response_code() and returns a plain array.
        // ViewRendererListener wraps a plain-array result in `new JsonResponse($result)` with no
        // explicit status, which defaults to 200 -- silently discarding the 404 set above.
        // FacilityRestController avoids this by using the newer createProcessingResultResponse(),
        // which returns a proper Response object instead of an array.
        // We assert the actual current behavior (200, empty data) rather than the "correct" 404,
        // so this test fails loudly if that behavior ever changes.
        $nonExistentUuid = $this->fixtureManager->getUnregisteredUuid();

        $actualResponse = $this->testClient->getOne(self::DRUG_API_ENDPOINT, $nonExistentUuid);
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEmpty($responseBody['data']);
    }

    #[Test]
    public function testGetOneInvalidUuidFormat(): void
    {
        // Same known bug as testGetOneNotFound() -- see comment there.
        $actualResponse = $this->testClient->getOne(self::DRUG_API_ENDPOINT, 'not-a-uuid');
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertEmpty($responseBody['data']);
    }
}
