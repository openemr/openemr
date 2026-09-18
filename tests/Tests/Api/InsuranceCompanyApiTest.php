<?php

/**
 * InsuranceCompany API Endpoint Tests
 *
 * The insurance company REST API has a pre-existing bug:
 * - GET one: Binary UUID in raw row causes JSON encoding error
 * (POST/PUT previously fataled on a missing InsuranceCompanyService::validate();
 * restored alongside the search parameter support.)
 * (GET all previously did not handle ProcessingResult; fixed by wiring
 * search parameters through to InsuranceCompanyService::search().
 * InsuranceEmployerSearchApiTest covers the GET all search behavior.)
 *
 * These tests document the remaining broken state.
 * Service-layer tests in InsuranceCompanyServiceTest and AddressServiceTest
 * cover the AddressData DTO integration that this PR introduces.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyApiTest extends TestCase
{
    private const API_ENDPOINT = "/apis/default/api/insurance_company";
    private const FIXTURE_NAME_PREFIX = "test-fixture-Validate";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // remove any insurance companies this test class created (the prefix
        // match also sweeps rows leaked by earlier runs)
        $rows = QueryUtils::fetchRecords(
            "SELECT `id` FROM `insurance_companies` WHERE `name` LIKE ?",
            [self::FIXTURE_NAME_PREFIX . "%"]
        );
        foreach ($rows as $row) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$row['id']]);
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testGetInsuranceTypes(): void
    {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->testClient->get("/apis/default/api/insurance_type");
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body, 'Should return at least one insurance type');
    }

    #[Test]
    public function testGetOneReturns404ForMissingId(): void
    {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->testClient->getOne(self::API_ENDPOINT, '999999999');
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithValidDataCreatesCompany(): void
    {
        // regression test: post() previously fataled because
        // InsuranceCompanyService::validate() did not exist
        // (empty-string optionals are omitted: the validator applies length
        // rules to present-but-empty values)
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'name' => self::FIXTURE_NAME_PREFIX . " Restored Insurance " . uniqid(),
            'ins_type_code' => '1',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithInvalidAddressReturns400(): void
    {
        // company fields valid, address invalid: exercises the second
        // validation branch, which converts a Particle ValidationResult
        // into a ProcessingResult for the 400 response
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'name' => self::FIXTURE_NAME_PREFIX . " Bad Address " . uniqid(),
            'ins_type_code' => '1',
            'line1' => 'X',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithMissingNameReturns400(): void
    {
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'ins_type_code' => '1',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
