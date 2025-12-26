<?php

/**
 * InsuranceApiTest - API integration tests for Vietnamese Insurance (BHYT) endpoints
 * AI-GENERATED CODE
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('vietnamese-pt')]
#[Group('vietnamese-api')]
class InsuranceApiTest extends TestCase
{
    private const INSURANCE_ENDPOINT = "/apis/default/vietnamese-pt/insurance";
    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testValidateBHYTCard(): void
    {
        $insuranceData = [
            'card_number' => 'DN1234567890123',
            'patient_id' => 1
        ];

        $response = $this->testClient->post(self::INSURANCE_ENDPOINT . '/validate', $insuranceData);

        $this->assertContains($response->getStatusCode(), [200, 201]);

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('valid', $body['data']);
    }

    #[Test]
    public function testCheckCoverage(): void
    {
        $coverageData = [
            'card_number' => 'DN1234567890123',
            'service_code' => 'PT001',
            'patient_id' => 1
        ];

        $response = $this->testClient->post(self::INSURANCE_ENDPOINT . '/coverage', $coverageData);

        $this->assertContains($response->getStatusCode(), [200, 201]);

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('covered', $body['data']);
    }

    #[Test]
    public function testGetInsuranceInfo(): void
    {
        $response = $this->testClient->get(self::INSURANCE_ENDPOINT, ['patient_id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    #[Test]
    public function testInvalidCardNumber(): void
    {
        $insuranceData = [
            'card_number' => 'INVALID',
            'patient_id' => 1
        ];

        $response = $this->testClient->post(self::INSURANCE_ENDPOINT . '/validate', $insuranceData);

        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200) {
            $this->assertFalse($body['data']['valid']);
        } else {
            $this->assertEquals(400, $response->getStatusCode());
        }
    }
}
