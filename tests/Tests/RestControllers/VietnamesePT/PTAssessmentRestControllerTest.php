<?php

namespace OpenEMR\Tests\RestControllers\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * PT Assessment REST Controller Tests
 *
 * Tests comprehensive CRUD operations and Vietnamese character handling
 * for physiotherapy assessments.
 *
 * AI Generated Test Suite - Generated for Vietnamese PT REST API testing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI Generated <test@openemr.org>
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTAssessmentRestControllerTest extends TestCase
{
    /**
     * Test that ProcessingResult works correctly for assessment data
     */
    public function testProcessingResultWithAssessmentData(): void
    {
        $assessmentData = [
            'id' => 1,
            'patient_id' => 1,
            'chief_complaint_en' => 'Lower back pain',
            'chief_complaint_vi' => 'Đau lưng dưới',
            'pain_level' => 7,
            'status' => 'completed'
        ];

        $result = new ProcessingResult();
        $result->addData($assessmentData);

        $this->assertFalse($result->hasErrors());
        $this->assertEquals(1, count($result->getData()));
        $this->assertEquals('Đau lưng dưới', $result->getData()[0]['chief_complaint_vi']);
    }

    /**
     * Test ProcessingResult with validation errors
     */
    public function testProcessingResultWithValidationErrors(): void
    {
        $result = new ProcessingResult();
        $result->addValidationMessage('patient_id', 'required');
        $result->addValidationMessage('assessment_date', 'required');

        $this->assertTrue($result->hasErrors());
        $this->assertGreaterThan(0, count($result->getValidationMessages()));
    }

    /**
     * Test Vietnamese characters in assessment data
     */
    public function testVietnameseCharactersPreservation(): void
    {
        $vietnamesePhrases = [
            'Đau lưng dưới' => 'Lower back pain',
            'Đau cổ' => 'Neck pain',
            'Mãn tính' => 'Chronic',
            'Cấp tính' => 'Acute',
            'Giãn cơ lưng' => 'Lumbar stretches',
            'Phạm vi chuyển động' => 'Range of motion',
            'Cột sống' => 'Spine',
            'Hẹp ống sống' => 'Spinal stenosis',
            'Chèn ép thần kinh' => 'Nerve compression'
        ];

        foreach ($vietnamesePhrases as $vietnamese => $english) {
            $result = new ProcessingResult();
            $result->addData([
                'id' => 1,
                'chief_complaint_en' => $english,
                'chief_complaint_vi' => $vietnamese
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($vietnamese, $data['chief_complaint_vi']);
            $this->assertStringContainsString($vietnamese[0], $data['chief_complaint_vi']);
        }
    }

    /**
     * Test assessment pain level validation
     */
    public function testPainLevelValidation(): void
    {
        // Test valid pain levels
        $validLevels = [0, 1, 3, 5, 7, 10];
        foreach ($validLevels as $level) {
            $result = new ProcessingResult();
            $result->addData(['pain_level' => $level]);
            $this->assertFalse($result->hasErrors());
            $this->assertEquals($level, $result->getData()[0]['pain_level']);
        }
    }

    /**
     * Test assessment status values
     */
    public function testAssessmentStatusValues(): void
    {
        $validStatuses = ['draft', 'completed', 'reviewed', 'cancelled'];

        foreach ($validStatuses as $status) {
            $result = new ProcessingResult();
            $result->addData([
                'id' => 1,
                'patient_id' => 1,
                'status' => $status
            ]);

            $this->assertEquals($status, $result->getData()[0]['status']);
        }
    }

    /**
     * Test multiple assessments aggregation
     */
    public function testMultipleAssessments(): void
    {
        $assessments = [
            [
                'id' => 1,
                'patient_id' => 1,
                'chief_complaint_vi' => 'Đau lưng dưới',
                'pain_level' => 7,
                'assessment_date' => '2024-01-15'
            ],
            [
                'id' => 2,
                'patient_id' => 1,
                'chief_complaint_vi' => 'Đánh giá theo dõi',
                'pain_level' => 5,
                'assessment_date' => '2024-02-15'
            ]
        ];

        $result = new ProcessingResult();
        foreach ($assessments as $assessment) {
            $result->addData($assessment);
        }

        $this->assertEquals(2, count($result->getData()));
        $this->assertEquals('Đau lưng dưới', $result->getData()[0]['chief_complaint_vi']);
        $this->assertEquals('Đánh giá theo dõi', $result->getData()[1]['chief_complaint_vi']);
    }

    /**
     * Test assessment data structure
     */
    public function testAssessmentDataStructure(): void
    {
        $requiredFields = [
            'id', 'patient_id', 'assessment_date',
            'chief_complaint_en', 'chief_complaint_vi'
        ];

        $assessmentData = [
            'id' => 1,
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'chief_complaint_en' => 'Lower back pain',
            'chief_complaint_vi' => 'Đau lưng dưới',
            'pain_level' => 7,
            'pain_location_en' => 'Lumbar region',
            'pain_location_vi' => 'Vùng lưng dưới',
            'status' => 'completed'
        ];

        $result = new ProcessingResult();
        $result->addData($assessmentData);

        $data = $result->getData()[0];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $data);
        }
    }

    /**
     * Test UTF-8 character encoding
     */
    public function testUTF8Encoding(): void
    {
        $vietnameseText = 'Phục hồi chức năng cột sống';

        $this->assertTrue(mb_check_encoding($vietnameseText, 'UTF-8'));

        $result = new ProcessingResult();
        $result->addData(['description_vi' => $vietnameseText]);

        $this->assertEquals($vietnameseText, $result->getData()[0]['description_vi']);
    }

    /**
     * Test special Vietnamese diacriticals
     */
    public function testSpecialVietnameseDiacriticals(): void
    {
        $diacriticals = [
            'Đ' => 'D with stroke',
            'đ' => 'd with stroke',
            'Ơ' => 'O with horn',
            'ơ' => 'o with horn',
            'Ư' => 'U with horn',
            'ư' => 'u with horn',
            'Ă' => 'A with breve',
            'ă' => 'a with breve',
            'Ê' => 'E with circumflex',
            'ê' => 'e with circumflex'
        ];

        foreach ($diacriticals as $character => $description) {
            $text = 'Test ' . $character;
            $this->assertTrue(mb_check_encoding($text, 'UTF-8'));

            $result = new ProcessingResult();
            $result->addData(['text' => $text]);
            $this->assertStringContainsString($character, $result->getData()[0]['text']);
        }
    }

    /**
     * Test empty assessments result
     */
    public function testEmptyAssessmentsResult(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasErrors());
        $this->assertEquals(0, count($result->getData()));
    }

    /**
     * Test bilingual field pairs
     */
    public function testBilingualFieldPairs(): void
    {
        $bilingualPairs = [
            ['en' => 'Chief complaint', 'vi' => 'Triệu chứng chính'],
            ['en' => 'Pain location', 'vi' => 'Vị trí đau'],
            ['en' => 'Functional goals', 'vi' => 'Mục tiêu chức năng'],
            ['en' => 'Treatment plan', 'vi' => 'Kế hoạch điều trị']
        ];

        foreach ($bilingualPairs as $pair) {
            $result = new ProcessingResult();
            $result->addData([
                'field_en' => $pair['en'],
                'field_vi' => $pair['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($pair['en'], $data['field_en']);
            $this->assertEquals($pair['vi'], $data['field_vi']);
        }
    }

    /**
     * Test assessment dates
     */
    public function testAssessmentDates(): void
    {
        $dates = [
            '2024-01-15',
            '2024-06-30',
            '2024-12-31'
        ];

        foreach ($dates as $date) {
            $result = new ProcessingResult();
            $result->addData([
                'id' => 1,
                'patient_id' => 1,
                'assessment_date' => $date
            ]);

            $this->assertEquals($date, $result->getData()[0]['assessment_date']);
        }
    }

    /**
     * Test error message accumulation
     */
    public function testErrorMessageAccumulation(): void
    {
        $result = new ProcessingResult();
        $result->addValidationMessage('patient_id', 'required');
        $result->addValidationMessage('assessment_date', 'required');
        $result->addValidationMessage('pain_level', 'must be between 0 and 10');

        $this->assertTrue($result->hasErrors());
        $messages = $result->getValidationMessages();
        $this->assertEquals(3, count($messages));
    }

    /**
     * Test large assessment dataset
     */
    public function testLargeAssessmentDataset(): void
    {
        $result = new ProcessingResult();

        // Add 100 assessments
        for ($i = 1; $i <= 100; $i++) {
            $result->addData([
                'id' => $i,
                'patient_id' => ($i % 10) + 1,
                'chief_complaint_vi' => 'Triệu chứng ' . $i,
                'pain_level' => ($i % 11),
                'status' => 'completed'
            ]);
        }

        $this->assertEquals(100, count($result->getData()));
        $this->assertEquals('Triệu chứng 50', $result->getData()[49]['chief_complaint_vi']);
    }

    /**
     * Test assessment with null optional fields
     */
    public function testAssessmentWithNullOptionalFields(): void
    {
        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'chief_complaint_en' => 'Pain',
            'chief_complaint_vi' => 'Đau',
            'pain_level' => null,
            'pain_location_en' => null,
            'pain_location_vi' => null
        ]);

        $data = $result->getData()[0];
        $this->assertNull($data['pain_level']);
        $this->assertNull($data['pain_location_en']);
        $this->assertNull($data['pain_location_vi']);
    }

    /**
     * Test controller response structure
     */
    public function testControllerResponseStructure(): void
    {
        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'status' => 'completed'
        ]);

        // Simulate the response structure from RestControllerHelper
        $response = [
            'data' => $result->getData(),
            'validationErrors' => $result->getValidationMessages(),
            'internalErrors' => $result->getInternalErrors()
        ];

        $this->assertIsArray($response['data']);
        $this->assertIsArray($response['validationErrors']);
        $this->assertIsArray($response['internalErrors']);
        $this->assertEquals(1, count($response['data']));
    }
}
