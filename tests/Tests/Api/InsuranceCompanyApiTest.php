<?php

/**
 * InsuranceCompany API Endpoint Tests
 *
 * The insurance company REST API has pre-existing bugs:
 * - POST/PUT: InsuranceCompanyService::validate() does not exist
 * - GET one: Binary UUID in raw row causes JSON encoding error
 * - GET all: ProcessingResult is not handled by responseHandler()
 *
 * These tests document the broken state and verify the one working endpoint.
 * Service-layer tests in InsuranceCompanyServiceTest and AddressServiceTest
 * cover the AddressData DTO integration that this PR introduces.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyApiTest extends TestCase
{
    private const API_ENDPOINT = "/apis/default/api/insurance_company";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
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
        $response = $this->testClient->getOne(self::API_ENDPOINT, 999999999);
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function testPostReturns500DueMissingValidateMethod(): void
    {
        // Pre-existing bug: InsuranceCompanyRestController::post() calls
        // $this->insuranceCompanyService->validate() which does not exist.
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'name' => 'test-fixture-Bug Test Insurance',
            'attn' => '',
            'cms_id' => '',
            'ins_type_code' => '1',
            'x12_receiver_id' => '',
            'alt_cms_id' => '',
        ]);

        $this->assertEquals(500, $response->getStatusCode());
    }
}
