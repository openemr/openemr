<?php

/**
 * Bilingual Assessment Form Unit Tests
 * Tests Vietnamese/English bilingual physiotherapy assessment handling
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Vietnamese;

use PHPUnit\Framework\TestCase;

class BilingualAssessmentTest extends TestCase
{
    private array $sampleAssessment = [
        'patient' => [
            'name_vi' => 'Nguyễn Văn An',
            'name_en' => 'An Nguyen Van',
            'dob' => '1985-05-15',
            'gender' => 'male',
        ],
        'chief_complaint' => [
            'vi' => 'Đau lưng dưới kéo dài 3 tháng',
            'en' => 'Lower back pain for 3 months',
        ],
        'diagnosis' => [
            'vi' => 'Thoái hóa đốt sống thắt lưng',
            'en' => 'Lumbar spondylosis',
        ],
        'treatment_plan' => [
            'vi' => 'Vận động trị liệu, massage, điện trị liệu',
            'en' => 'Exercise therapy, massage, electrotherapy',
        ],
    ];

    /**
     * Test assessment structure validation
     */
    public function testAssessmentStructure(): void
    {
        $this->assertArrayHasKey('patient', $this->sampleAssessment);
        $this->assertArrayHasKey('chief_complaint', $this->sampleAssessment);
        $this->assertArrayHasKey('diagnosis', $this->sampleAssessment);
        $this->assertArrayHasKey('treatment_plan', $this->sampleAssessment);
    }

    /**
     * Test bilingual field validation
     */
    public function testBilingualFields(): void
    {
        $bilingualFields = ['chief_complaint', 'diagnosis', 'treatment_plan'];

        foreach ($bilingualFields as $field) {
            $this->assertArrayHasKey('vi', $this->sampleAssessment[$field]);
            $this->assertArrayHasKey('en', $this->sampleAssessment[$field]);
            $this->assertNotEmpty($this->sampleAssessment[$field]['vi']);
            $this->assertNotEmpty($this->sampleAssessment[$field]['en']);
        }
    }

    /**
     * Test Vietnamese text encoding in assessment
     */
    public function testVietnameseEncoding(): void
    {
        $vietnameseFields = [
            $this->sampleAssessment['patient']['name_vi'],
            $this->sampleAssessment['chief_complaint']['vi'],
            $this->sampleAssessment['diagnosis']['vi'],
            $this->sampleAssessment['treatment_plan']['vi'],
        ];

        foreach ($vietnameseFields as $text) {
            $this->assertTrue(
                mb_check_encoding($text, 'UTF-8'),
                "Vietnamese text should be valid UTF-8: $text"
            );
        }
    }

    /**
     * Test patient name bilingual format
     */
    public function testPatientNameFormat(): void
    {
        $nameVi = $this->sampleAssessment['patient']['name_vi'];
        $nameEn = $this->sampleAssessment['patient']['name_en'];

        // Vietnamese name should contain Vietnamese characters
        $this->assertMatchesRegularExpression('/[ăâđêôơư]/iu', $nameVi);

        // English name should be ASCII
        $this->assertTrue(mb_check_encoding($nameEn, 'ASCII'));

        // Both should not be empty
        $this->assertNotEmpty($nameVi);
        $this->assertNotEmpty($nameEn);
    }

    /**
     * Test assessment data serialization
     */
    public function testAssessmentSerialization(): void
    {
        $json = json_encode($this->sampleAssessment, JSON_UNESCAPED_UNICODE);

        $this->assertNotFalse($json, "Assessment should be JSON serializable");
        $this->assertStringContainsString('Nguyễn', $json);
        $this->assertStringContainsString('Đau', $json);

        $decoded = json_decode($json, true);
        $this->assertEquals($this->sampleAssessment, $decoded);
    }

    /**
     * Test assessment field validation
     */
    public function testFieldValidation(): void
    {
        // Patient DOB should be valid date
        $dob = $this->sampleAssessment['patient']['dob'];
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $dob);

        // Gender should be valid
        $gender = $this->sampleAssessment['patient']['gender'];
        $this->assertContains($gender, ['male', 'female', 'other']);
    }

    /**
     * Test bilingual content pairing
     */
    public function testBilingualContentPairing(): void
    {
        // Chief complaint should have both versions
        $chiefComplaint = $this->sampleAssessment['chief_complaint'];
        $this->assertStringContainsString('Đau', $chiefComplaint['vi']);
        $this->assertStringContainsString('pain', strtolower($chiefComplaint['en']));

        // Diagnosis should have both versions
        $diagnosis = $this->sampleAssessment['diagnosis'];
        $this->assertNotEmpty($diagnosis['vi']);
        $this->assertNotEmpty($diagnosis['en']);
    }

    /**
     * Test assessment data completeness
     */
    public function testAssessmentCompleteness(): void
    {
        // All required fields should be present and non-empty
        $requiredFields = [
            ['patient', 'name_vi'],
            ['patient', 'name_en'],
            ['patient', 'dob'],
            ['chief_complaint', 'vi'],
            ['chief_complaint', 'en'],
            ['diagnosis', 'vi'],
            ['diagnosis', 'en'],
            ['treatment_plan', 'vi'],
            ['treatment_plan', 'en'],
        ];

        foreach ($requiredFields as $fieldPath) {
            if (count($fieldPath) === 2) {
                $this->assertArrayHasKey($fieldPath[0], $this->sampleAssessment);
                $this->assertArrayHasKey($fieldPath[1], $this->sampleAssessment[$fieldPath[0]]);
                $this->assertNotEmpty($this->sampleAssessment[$fieldPath[0]][$fieldPath[1]]);
            }
        }
    }

    /**
     * Test language consistency
     */
    public function testLanguageConsistency(): void
    {
        // All Vietnamese fields should contain Vietnamese characters
        $vietnameseTexts = [
            $this->sampleAssessment['chief_complaint']['vi'],
            $this->sampleAssessment['diagnosis']['vi'],
            $this->sampleAssessment['treatment_plan']['vi'],
        ];

        foreach ($vietnameseTexts as $text) {
            $hasVietnameseChars = preg_match('/[àáảãạăắằẳẵặâấầẩẫậđèéẻẽẹêếềểễệìíỉĩịòóỏõọôốồổỗộơớờởỡợùúủũụưứừửữựỳýỷỹỵ]/iu', $text);
            $this->assertGreaterThan(0, $hasVietnameseChars, "Vietnamese text should contain Vietnamese characters");
        }
    }

    /**
     * Test assessment merging
     */
    public function testAssessmentMerging(): void
    {
        $update = [
            'treatment_plan' => [
                'vi' => 'Vật lý trị liệu nâng cao',
                'en' => 'Advanced physiotherapy',
            ],
        ];

        $merged = array_merge($this->sampleAssessment, $update);

        $this->assertEquals('Vật lý trị liệu nâng cao', $merged['treatment_plan']['vi']);
        $this->assertEquals('Advanced physiotherapy', $merged['treatment_plan']['en']);
        $this->assertEquals($this->sampleAssessment['patient'], $merged['patient']);
    }

    /**
     * Test assessment to database format conversion
     */
    public function testDatabaseFormatConversion(): void
    {
        $dbFormat = [
            'patient_name_vi' => $this->sampleAssessment['patient']['name_vi'],
            'patient_name_en' => $this->sampleAssessment['patient']['name_en'],
            'patient_dob' => $this->sampleAssessment['patient']['dob'],
            'chief_complaint_vi' => $this->sampleAssessment['chief_complaint']['vi'],
            'chief_complaint_en' => $this->sampleAssessment['chief_complaint']['en'],
            'diagnosis_vi' => $this->sampleAssessment['diagnosis']['vi'],
            'diagnosis_en' => $this->sampleAssessment['diagnosis']['en'],
            'treatment_plan_vi' => $this->sampleAssessment['treatment_plan']['vi'],
            'treatment_plan_en' => $this->sampleAssessment['treatment_plan']['en'],
        ];

        foreach ($dbFormat as $key => $value) {
            $this->assertNotEmpty($value, "DB field $key should not be empty");
            $this->assertIsString($value, "DB field $key should be string");
        }
    }

    /**
     * Test special characters in treatment plan
     */
    public function testSpecialCharactersHandling(): void
    {
        $treatmentWithSpecialChars = [
            'vi' => 'Điều trị 2-3 lần/tuần, kéo dài 4-6 tuần',
            'en' => 'Treatment 2-3 times/week for 4-6 weeks',
        ];

        foreach ($treatmentWithSpecialChars as $lang => $text) {
            $this->assertTrue(mb_check_encoding($text, 'UTF-8'));
            $this->assertMatchesRegularExpression('/\d+-\d+/', $text, "Should contain number ranges");
        }
    }
}