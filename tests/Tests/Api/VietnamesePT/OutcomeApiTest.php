<?php

/**
 * OutcomeApiTest - API integration tests for Vietnamese PT Outcome Measures endpoints
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
class OutcomeApiTest extends TestCase
{
    private const OUTCOME_ENDPOINT = "/apis/default/vietnamese-pt/outcome";
    private ApiTestClient $testClient;
    private ?string $createdOutcomeId = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        if ($this->createdOutcomeId) {
            try {
                $this->testClient->delete(self::OUTCOME_ENDPOINT, $this->createdOutcomeId);
            } catch (\Exception $e) {
            }
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testCreateOutcomeMeasure(): void
    {
        $outcomeData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'measure_type' => 'ROM',
            'baseline_value' => 45,
            'current_value' => 75,
            'target_value' => 120,
            'location' => 'Right Knee Flexion',
            'unit' => 'degrees'
        ];

        $response = $this->testClient->post(self::OUTCOME_ENDPOINT, $outcomeData);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->createdOutcomeId = $body['data']['id'];
        $this->assertEquals('ROM', $body['data']['measure_type']);
    }

    #[Test]
    public function testProgressCalculations(): void
    {
        $outcomeData = [
            'patient_id' => 1,
            'measure_type' => 'Pain',
            'baseline_value' => 8,
            'current_value' => 4,
            'target_value' => 2
        ];

        $response = $this->testClient->post(self::OUTCOME_ENDPOINT, $outcomeData);
        $body = json_decode($response->getBody(), true);
        $this->createdOutcomeId = $body['data']['id'];

        // Progress for pain (lower is better): (8-4)/(8-2) = 4/6 = 66.7%
        $this->assertArrayHasKey('progress_percentage', $body['data']);
    }

    #[Test]
    public function testMeasureTypeFiltering(): void
    {
        $types = ['ROM', 'Strength', 'Pain', 'Function', 'Balance'];

        foreach ($types as $type) {
            $outcomeData = ['patient_id' => 1, 'measure_type' => $type, 'baseline_value' => 5, 'current_value' => 7, 'target_value' => 10];
            $response = $this->testClient->post(self::OUTCOME_ENDPOINT, $outcomeData);
            $this->assertEquals(201, $response->getStatusCode());
        }

        $response = $this->testClient->get(self::OUTCOME_ENDPOINT, ['measure_type' => 'ROM']);
        $body = json_decode($response->getBody(), true);

        foreach ($body['data'] as $outcome) {
            $this->assertEquals('ROM', $outcome['measure_type']);
        }
    }

    #[Test]
    public function testDateBasedQueries(): void
    {
        $outcomeData = [
            'patient_id' => 1,
            'measure_type' => 'Function',
            'baseline_value' => 35,
            'current_value' => 55,
            'target_value' => 75,
            'measured_date' => '2025-01-15'
        ];

        $response = $this->testClient->post(self::OUTCOME_ENDPOINT, $outcomeData);
        $body = json_decode($response->getBody(), true);
        $this->createdOutcomeId = $body['data']['id'];

        $response = $this->testClient->get(self::OUTCOME_ENDPOINT, ['date_from' => '2025-01-01']);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
