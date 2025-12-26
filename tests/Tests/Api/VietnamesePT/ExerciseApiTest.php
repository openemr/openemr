<?php

/**
 * ExerciseApiTest - API integration tests for Vietnamese PT Exercise endpoints
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
class ExerciseApiTest extends TestCase
{
    private const EXERCISE_ENDPOINT = "/apis/default/vietnamese-pt/exercise";
    private ApiTestClient $testClient;
    private ?string $createdExerciseId = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        if ($this->createdExerciseId) {
            try {
                $this->testClient->delete(self::EXERCISE_ENDPOINT, $this->createdExerciseId);
            } catch (\Exception $e) {
            }
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testCreateExercisePrescription(): void
    {
        $exerciseData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'exercise_name_en' => 'Lumbar Flexion Stretch',
            'exercise_name_vi' => 'Bài tập duỗi cột sống thắt lưng',
            'sets' => 3,
            'reps' => 10,
            'duration_minutes' => 30,
            'frequency' => 'Daily',
            'intensity' => 'Moderate',
            'description_en' => 'Lie on back, bring knees to chest',
            'description_vi' => 'Nằm ngửa, kéo đầu gối về ngực'
        ];

        $response = $this->testClient->post(self::EXERCISE_ENDPOINT, $exerciseData);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->createdExerciseId = $body['data']['id'];
        $this->assertEquals($exerciseData['exercise_name_en'], $body['data']['exercise_name_en']);
    }

    #[Test]
    public function testGetExercisePrescription(): void
    {
        $exerciseData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'exercise_name_en' => 'Test Exercise',
            'exercise_name_vi' => 'Bài tập thử nghiệm',
            'sets' => 2,
            'reps' => 15
        ];

        $createResponse = $this->testClient->post(self::EXERCISE_ENDPOINT, $exerciseData);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdExerciseId = $createBody['data']['id'];

        $response = $this->testClient->getOne(self::EXERCISE_ENDPOINT, $this->createdExerciseId);
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function testUpdateExercisePrescription(): void
    {
        $exerciseData = ['patient_id' => 1, 'exercise_name_en' => 'Original', 'exercise_name_vi' => 'Gốc', 'sets' => 3, 'reps' => 10];
        $createResponse = $this->testClient->post(self::EXERCISE_ENDPOINT, $exerciseData);
        $createBody = json_decode($createResponse->getBody(), true);
        $this->createdExerciseId = $createBody['data']['id'];

        $updateData = $exerciseData;
        $updateData['sets'] = 4;
        $updateData['reps'] = 12;

        $response = $this->testClient->put(self::EXERCISE_ENDPOINT, $this->createdExerciseId, $updateData);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals(4, $body['data']['sets']);
        $this->assertEquals(12, $body['data']['reps']);
    }

    #[Test]
    public function testVietnameseCharactersInExercise(): void
    {
        $exerciseData = [
            'patient_id' => 1,
            'exercise_name_vi' => 'Bài tập tăng cường cơ lưng dưới',
            'exercise_name_en' => 'Lower back strengthening',
            'description_vi' => 'Nằm sấp, nâng cánh tay và chân đối diện lên cao',
            'sets' => 3,
            'reps' => 10
        ];

        $response = $this->testClient->post(self::EXERCISE_ENDPOINT, $exerciseData);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->createdExerciseId = $body['data']['id'];
        $this->assertStringContainsString('tăng cường', $body['data']['exercise_name_vi']);
    }

    #[Test]
    public function testInvalidExerciseData(): void
    {
        $invalidData = ['patient_id' => 1]; // Missing required fields

        $response = $this->testClient->post(self::EXERCISE_ENDPOINT, $invalidData);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
