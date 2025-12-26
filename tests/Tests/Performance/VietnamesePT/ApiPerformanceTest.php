<?php

/**
 * ApiPerformanceTest - Performance benchmarks for Vietnamese PT API endpoints
 * AI-GENERATED CODE
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Performance\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('vietnamese-pt')]
#[Group('performance')]
class ApiPerformanceTest extends TestCase
{
    private const THRESHOLD_API_RESPONSE = 200; // milliseconds
    private const THRESHOLD_CONCURRENT = 500; // milliseconds for concurrent requests

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
    public function testAssessmentEndpointResponseTime(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/assessment";

        $startTime = microtime(true);
        $response = $this->testClient->get($endpoint);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLessThan(
            self::THRESHOLD_API_RESPONSE,
            $duration,
            "GET assessment endpoint took {$duration}ms, should be < " . self::THRESHOLD_API_RESPONSE . "ms"
        );
    }

    #[Test]
    public function testExerciseEndpointResponseTime(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/exercise";

        $startTime = microtime(true);
        $response = $this->testClient->get($endpoint);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLessThan(self::THRESHOLD_API_RESPONSE, $duration);
    }

    #[Test]
    public function testConcurrentRequestHandling(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/assessment";

        $startTime = microtime(true);

        // Simulate 10 concurrent requests (sequential for test simplicity)
        for ($i = 0; $i < 10; $i++) {
            $this->testClient->get($endpoint);
        }

        $endTime = microtime(true);
        $totalDuration = ($endTime - $startTime) * 1000;
        $avgDuration = $totalDuration / 10;

        $this->assertLessThan(
            self::THRESHOLD_API_RESPONSE,
            $avgDuration,
            "Average concurrent request time: {$avgDuration}ms, should be < " . self::THRESHOLD_API_RESPONSE . "ms"
        );
    }

    #[Test]
    public function testCreateAndRetrievePerformance(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/assessment";

        $assessmentData = [
            'patient_id' => 1,
            'chief_complaint_en' => 'Performance test',
            'chief_complaint_vi' => 'Kiểm tra hiệu suất',
            'pain_level' => 5
        ];

        $startTime = microtime(true);
        $createResponse = $this->testClient->post($endpoint, $assessmentData);
        $endTime = microtime(true);

        $createDuration = ($endTime - $startTime) * 1000;

        $this->assertEquals(201, $createResponse->getStatusCode());
        $this->assertLessThan(self::THRESHOLD_API_RESPONSE, $createDuration);

        $createBody = json_decode($createResponse->getBody(), true);
        $id = $createBody['data']['id'];

        // Test retrieval
        $startTime = microtime(true);
        $getResponse = $this->testClient->getOne($endpoint, $id);
        $endTime = microtime(true);

        $getDuration = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $getResponse->getStatusCode());
        $this->assertLessThan(self::THRESHOLD_API_RESPONSE, $getDuration);

        // Cleanup
        $this->testClient->delete($endpoint, $id);
    }

    #[Test]
    public function testMedicalTermsLookupPerformance(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/medical-terms";

        $startTime = microtime(true);
        $response = $this->testClient->get($endpoint, ['english_term' => 'pain']);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLessThan(
            100,
            $duration,
            "Medical terms lookup took {$duration}ms, should be < 100ms"
        );
    }

    #[Test]
    public function testMemoryUsageDuringApiCalls(): void
    {
        $endpoint = "/apis/default/vietnamese-pt/assessment";
        $initialMemory = memory_get_usage();

        for ($i = 0; $i < 20; $i++) {
            $this->testClient->get($endpoint);
        }

        $peakMemory = memory_get_peak_usage();
        $memoryIncrease = ($peakMemory - $initialMemory) / 1024 / 1024; // MB

        $this->assertLessThan(
            10,
            $memoryIncrease,
            "Memory increase during 20 API calls: {$memoryIncrease}MB, should be < 10MB"
        );
    }
}
