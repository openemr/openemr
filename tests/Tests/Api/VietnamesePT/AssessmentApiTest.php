<?php

/**
 * AssessmentApiTest - API integration tests for Vietnamese PT Assessment endpoints
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code (AI Assistant)
 * @copyright Copyright (c) 2025 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-GENERATED CODE - START
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('vietnamese-pt')]
#[Group('vietnamese-api')]
class AssessmentApiTest extends TestCase
{
    private const ASSESSMENT_ENDPOINT = "/apis/default/vietnamese-pt/assessment";
    private ApiTestClient $testClient;
    private array $testAssessment;
    private ?string $createdAssessmentId = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->testAssessment = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'chief_complaint_en' => 'Lower back pain radiating to left leg',
            'chief_complaint_vi' => 'Đau lưng dưới lan xuống chân trái',
            'pain_level' => 7,
            'subjective_en' => 'Pain worsens with prolonged sitting and bending',
            'subjective_vi' => 'Đau tăng khi ngồi lâu và cúi người',
            'objective_en' => 'Limited lumbar flexion, positive SLR left',
            'objective_vi' => 'Hạn chế gập thắt lưng, SLR dương tính bên trái',
            'assessment_en' => 'Lumbar radiculopathy L5-S1',
            'assessment_vi' => 'Đau dây thần kinh rễ thắt lưng L5-S1',
            'plan_en' => 'Manual therapy, core strengthening, McKenzie exercises',
            'plan_vi' => 'Trị liệu thủ công, tăng cường cơ core, bài tập McKenzie',
            'language_preference' => 'vi'
        ];
    }

    protected function tearDown(): void
    {
        // Clean up created assessment
        if ($this->createdAssessmentId) {
            try {
                $this->testClient->delete(self::ASSESSMENT_ENDPOINT, $this->createdAssessmentId);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }

        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testCreateAssessment(): void
    {
        $response = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);

        $this->assertEquals(201, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($responseBody['validationErrors'] ?? []));
        $this->assertEquals(0, count($responseBody['internalErrors'] ?? []));

        $this->assertArrayHasKey('data', $responseBody);
        $data = $responseBody['data'];

        $this->assertArrayHasKey('id', $data);
        $this->createdAssessmentId = $data['id'];

        $this->assertEquals($this->testAssessment['chief_complaint_en'], $data['chief_complaint_en']);
        $this->assertEquals($this->testAssessment['chief_complaint_vi'], $data['chief_complaint_vi']);
        $this->assertEquals($this->testAssessment['pain_level'], $data['pain_level']);
    }

    #[Test]
    public function testGetOneAssessment(): void
    {
        // Create assessment first
        $createResponse = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);
        $createBody = json_decode($createResponse->getBody(), true);
        $assessmentId = $createBody['data']['id'];
        $this->createdAssessmentId = $assessmentId;

        // Get the assessment
        $response = $this->testClient->getOne(self::ASSESSMENT_ENDPOINT, $assessmentId);

        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($responseBody['validationErrors'] ?? []));
        $this->assertEquals(0, count($responseBody['internalErrors'] ?? []));

        $data = $responseBody['data'];
        $this->assertEquals($assessmentId, $data['id']);
        $this->assertEquals($this->testAssessment['chief_complaint_en'], $data['chief_complaint_en']);
    }

    #[Test]
    public function testGetAllAssessments(): void
    {
        // Create a test assessment
        $createResponse = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdAssessmentId = $createBody['data']['id'];

        // Get all assessments
        $response = $this->testClient->get(self::ASSESSMENT_ENDPOINT);

        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $responseBody);
        $this->assertIsArray($responseBody['data']);
        $this->assertGreaterThan(0, count($responseBody['data']));
    }

    #[Test]
    public function testUpdateAssessment(): void
    {
        // Create assessment first
        $createResponse = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);
        $createBody = json_decode($createResponse->getBody(), true);
        $assessmentId = $createBody['data']['id'];
        $this->createdAssessmentId = $assessmentId;

        // Update the assessment
        $updateData = $this->testAssessment;
        $updateData['pain_level'] = 5;
        $updateData['subjective_en'] = 'Pain improving with therapy';
        $updateData['subjective_vi'] = 'Đau giảm sau điều trị';

        $response = $this->testClient->put(self::ASSESSMENT_ENDPOINT, $assessmentId, $updateData);

        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $data = $responseBody['data'];

        $this->assertEquals(5, $data['pain_level']);
        $this->assertEquals('Pain improving with therapy', $data['subjective_en']);
        $this->assertEquals('Đau giảm sau điều trị', $data['subjective_vi']);
    }

    #[Test]
    public function testDeleteAssessment(): void
    {
        // Create assessment first
        $createResponse = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);
        $createBody = json_decode($createResponse->getBody(), true);
        $assessmentId = $createBody['data']['id'];

        // Delete the assessment
        $response = $this->testClient->delete(self::ASSESSMENT_ENDPOINT, $assessmentId);

        $this->assertEquals(200, $response->getStatusCode());

        // Verify it's deleted
        $getResponse = $this->testClient->getOne(self::ASSESSMENT_ENDPOINT, $assessmentId);
        $this->assertEquals(404, $getResponse->getStatusCode());

        $this->createdAssessmentId = null; // Already deleted
    }

    #[Test]
    public function testVietnameseCharacterHandling(): void
    {
        $vietnameseData = $this->testAssessment;
        $vietnameseData['chief_complaint_vi'] = 'Đau khớp gối, sưng phù, hạn chế vận động';
        $vietnameseData['subjective_vi'] = 'Bệnh nhân báo cáo đau tăng khi lên xuống cầu thang';
        $vietnameseData['objective_vi'] = 'Sưng nhẹ khớp gối phải, ấn đau dọc khe khớp trong';

        $response = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $vietnameseData);

        $this->assertEquals(201, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $data = $responseBody['data'];
        $this->createdAssessmentId = $data['id'];

        // Verify Vietnamese characters are preserved
        $this->assertEquals('Đau khớp gối, sưng phù, hạn chế vận động', $data['chief_complaint_vi']);
        $this->assertStringContainsString('đau tăng', $data['subjective_vi']);
        $this->assertStringContainsString('Sưng nhẹ', $data['objective_vi']);
    }

    #[Test]
    public function testInvalidAssessmentMissingRequiredFields(): void
    {
        $invalidData = [
            'patient_id' => 1
            // Missing other required fields
        ];

        $response = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $invalidData);

        $this->assertEquals(400, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $this->assertGreaterThan(0, count($responseBody['validationErrors'] ?? []));
    }

    #[Test]
    public function testUnauthorizedAccess(): void
    {
        // Remove auth token
        $this->testClient->removeAuthToken();

        $response = $this->testClient->get(self::ASSESSMENT_ENDPOINT);

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function testGetNonExistentAssessment(): void
    {
        $response = $this->testClient->getOne(self::ASSESSMENT_ENDPOINT, '99999');

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function testFilterAssessmentsByPatient(): void
    {
        // Create assessment
        $createResponse = $this->testClient->post(self::ASSESSMENT_ENDPOINT, $this->testAssessment);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdAssessmentId = $createBody['data']['id'];

        // Get assessments filtered by patient
        $response = $this->testClient->get(self::ASSESSMENT_ENDPOINT, ['patient_id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = json_decode($response->getBody(), true);
        $data = $responseBody['data'];

        $this->assertIsArray($data);

        foreach ($data as $assessment) {
            $this->assertEquals(1, $assessment['patient_id']);
        }
    }
}

/**
 * AI-GENERATED CODE - END
 */
