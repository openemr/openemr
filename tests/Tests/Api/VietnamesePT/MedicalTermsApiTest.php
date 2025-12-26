<?php

/**
 * MedicalTermsApiTest - API integration tests for Vietnamese Medical Terms endpoints
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
class MedicalTermsApiTest extends TestCase
{
    private const TERMS_ENDPOINT = "/apis/default/vietnamese-pt/medical-terms";
    private const TRANSLATION_ENDPOINT = "/apis/default/vietnamese-pt/translation";
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
    public function testGetAllMedicalTerms(): void
    {
        $response = $this->testClient->get(self::TERMS_ENDPOINT);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
        $this->assertGreaterThan(0, count($body['data']));
    }

    #[Test]
    public function testLookupVietnameseTerm(): void
    {
        $response = $this->testClient->get(self::TERMS_ENDPOINT, ['english_term' => 'pain']);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $data = $body['data'];

        $this->assertNotEmpty($data);
        $this->assertEquals('pain', strtolower($data[0]['english_term']));
        $this->assertNotEmpty($data[0]['vietnamese_term']);
        $this->assertStringContainsString('đau', strtolower($data[0]['vietnamese_term']));
    }

    #[Test]
    public function testLookupEnglishTerm(): void
    {
        $response = $this->testClient->get(self::TERMS_ENDPOINT, ['vietnamese_term' => 'đau']);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $data = $body['data'];

        $this->assertNotEmpty($data);
        $this->assertStringContainsString('đau', strtolower($data[0]['vietnamese_term']));
    }

    #[Test]
    public function testTranslateEnglishToVietnamese(): void
    {
        $translationData = [
            'text' => 'pain',
            'source_language' => 'en',
            'target_language' => 'vi'
        ];

        $response = $this->testClient->post(self::TRANSLATION_ENDPOINT, $translationData);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('translation', $body['data']);
        $this->assertStringContainsString('đau', strtolower($body['data']['translation']));
    }

    #[Test]
    public function testTranslateVietnameseToEnglish(): void
    {
        $translationData = [
            'text' => 'đau',
            'source_language' => 'vi',
            'target_language' => 'en'
        ];

        $response = $this->testClient->post(self::TRANSLATION_ENDPOINT, $translationData);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('translation', $body['data']);
    }

    #[Test]
    public function testFuzzyMatching(): void
    {
        // Test partial match
        $response = $this->testClient->get(self::TERMS_ENDPOINT, ['english_term' => 'back']);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $data = $body['data'];

        // Should match "back", "lower back", "upper back", etc.
        $this->assertGreaterThan(0, count($data));
    }

    #[Test]
    public function testCommonPhysiotherapyTerms(): void
    {
        $commonTerms = ['pain', 'range of motion', 'strength', 'flexibility', 'balance', 'posture'];

        foreach ($commonTerms as $term) {
            $response = $this->testClient->get(self::TERMS_ENDPOINT, ['english_term' => $term]);

            $body = json_decode($response->getBody(), true);
            $data = $body['data'];

            $this->assertGreaterThan(
                0,
                count($data),
                "Common PT term '$term' should have Vietnamese translation"
            );
        }
    }

    #[Test]
    public function testSearchByCategory(): void
    {
        $response = $this->testClient->get(self::TERMS_ENDPOINT, ['category' => 'musculoskeletal']);

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);

        if (isset($body['data']) && count($body['data']) > 0) {
            foreach ($body['data'] as $term) {
                $this->assertEquals('musculoskeletal', $term['category']);
            }
        }
    }
}
