<?php

/**
 * TreatmentPlanApiTest - API integration tests for Vietnamese PT Treatment Plan endpoints
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
class TreatmentPlanApiTest extends TestCase
{
    private const PLAN_ENDPOINT = "/apis/default/vietnamese-pt/treatment-plan";
    private ApiTestClient $testClient;
    private ?string $createdPlanId = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        if ($this->createdPlanId) {
            try {
                $this->testClient->delete(self::PLAN_ENDPOINT, $this->createdPlanId);
            } catch (\Exception $e) {
            }
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testCreateTreatmentPlan(): void
    {
        $planData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'diagnosis_en' => 'Chronic lower back pain',
            'diagnosis_vi' => 'Đau lưng mãn tính',
            'goals_en' => 'Reduce pain, increase ROM',
            'goals_vi' => 'Giảm đau, tăng biên độ vận động',
            'frequency' => '3 times per week',
            'duration_weeks' => 8,
            'status' => 'Active'
        ];

        $response = $this->testClient->post(self::PLAN_ENDPOINT, $planData);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->createdPlanId = $body['data']['id'];
        $this->assertEquals('Active', $body['data']['status']);
    }

    #[Test]
    public function testStatusTransitions(): void
    {
        $planData = ['patient_id' => 1, 'diagnosis_en' => 'Test', 'diagnosis_vi' => 'Thử nghiệm', 'status' => 'Active'];
        $createResponse = $this->testClient->post(self::PLAN_ENDPOINT, $planData);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdPlanId = $createBody['data']['id'];

        foreach (['Completed', 'On Hold', 'Active'] as $status) {
            $updateData = $planData;
            $updateData['status'] = $status;
            $response = $this->testClient->put(self::PLAN_ENDPOINT, $this->createdPlanId, $updateData);
            $body = json_decode($response->getBody(), true);
            $this->assertEquals($status, $body['data']['status']);
        }
    }

    #[Test]
    public function testDateRangeQueries(): void
    {
        $planData = [
            'patient_id' => 1,
            'diagnosis_en' => 'Test',
            'diagnosis_vi' => 'Thử nghiệm',
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-01'
        ];

        $createResponse = $this->testClient->post(self::PLAN_ENDPOINT, $planData);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdPlanId = $createBody['data']['id'];

        $response = $this->testClient->get(self::PLAN_ENDPOINT, ['start_date' => '2025-01-01']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function testFilterByPatient(): void
    {
        $planData = ['patient_id' => 1, 'diagnosis_en' => 'Test', 'diagnosis_vi' => 'Thử nghiệm'];
        $createResponse = $this->testClient->post(self::PLAN_ENDPOINT, $planData);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdPlanId = $createBody['data']['id'];

        $response = $this->testClient->get(self::PLAN_ENDPOINT, ['patient_id' => 1]);
        $body = json_decode($response->getBody(), true);

        $this->assertIsArray($body['data']);
        foreach ($body['data'] as $plan) {
            $this->assertEquals(1, $plan['patient_id']);
        }
    }
}
