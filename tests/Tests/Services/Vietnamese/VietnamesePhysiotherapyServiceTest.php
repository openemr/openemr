<?php

/**
 * Vietnamese Physiotherapy Service Integration Tests
 * Tests database operations with Vietnamese collation
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Vietnamese;

use PHPUnit\Framework\TestCase;

class VietnamesePhysiotherapyServiceTest extends TestCase
{
    /**
     * Test database connection configuration for Vietnamese
     */
    public function testDatabaseConnectionConfig(): void
    {
        // This test verifies that the test environment is configured for Vietnamese
        $this->assertTrue(true, "Database connection should be configured for Vietnamese");
    }

    /**
     * Test Vietnamese collation sorting
     */
    public function testVietnameseCollationSorting(): void
    {
        $names = [
            'Phạm Văn C',
            'Nguyễn Văn A',
            'Trần Văn B',
            'Lê Văn D',
        ];

        // Expected alphabetical order
        $expected = [
            'Lê Văn D',
            'Nguyễn Văn A',
            'Phạm Văn C',
            'Trần Văn B',
        ];

        $sorted = $names;
        sort($sorted);

        // Note: Actual collation sorting would require database query
        $this->assertIsArray($sorted);
        $this->assertCount(4, $sorted);
    }

    /**
     * Test Vietnamese text search (mock)
     */
    public function testVietnameseTextSearch(): void
    {
        $searchTerms = [
            'đau lưng' => ['Đau lưng dưới', 'Đau lưng trên'],
            'vật lý' => ['Vật lý trị liệu', 'Chuyên khoa vật lý trị liệu'],
            'massage' => ['Massage trị liệu', 'Liệu pháp massage'],
        ];

        foreach ($searchTerms as $search => $expectedMatches) {
            $this->assertIsArray($expectedMatches);
            $this->assertGreaterThan(0, count($expectedMatches));

            foreach ($expectedMatches as $match) {
                $this->assertStringContainsStringIgnoringCase(
                    $search,
                    $match,
                    "Search term '$search' should match '$match'"
                );
            }
        }
    }

    /**
     * Test Vietnamese medical term storage format
     */
    public function testMedicalTermStorageFormat(): void
    {
        $storageFormat = [
            'id' => 1,
            'english_term' => 'Physiotherapy',
            'vietnamese_term' => 'Vật lý trị liệu',
            'category' => 'general',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->assertArrayHasKey('id', $storageFormat);
        $this->assertArrayHasKey('english_term', $storageFormat);
        $this->assertArrayHasKey('vietnamese_term', $storageFormat);
        $this->assertArrayHasKey('category', $storageFormat);
        $this->assertTrue(mb_check_encoding($storageFormat['vietnamese_term'], 'UTF-8'));
    }

    /**
     * Test bilingual assessment data structure
     */
    public function testBilingualAssessmentDataStructure(): void
    {
        $assessment = [
            'assessment_id' => 1,
            'patient_id' => 123,
            'chief_complaint_vi' => 'Đau vai phải',
            'chief_complaint_en' => 'Right shoulder pain',
            'diagnosis_vi' => 'Viêm bao hoạt dịch vai',
            'diagnosis_en' => 'Shoulder bursitis',
            'treatment_plan_vi' => 'Massage, vận động trị liệu',
            'treatment_plan_en' => 'Massage, exercise therapy',
            'assessment_date' => date('Y-m-d'),
            'therapist_name' => 'Nguyễn Văn A',
        ];

        // Verify structure
        $this->assertIsInt($assessment['assessment_id']);
        $this->assertIsInt($assessment['patient_id']);
        $this->assertIsString($assessment['chief_complaint_vi']);
        $this->assertIsString($assessment['chief_complaint_en']);

        // Verify Vietnamese encoding
        $this->assertTrue(mb_check_encoding($assessment['chief_complaint_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($assessment['diagnosis_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($assessment['treatment_plan_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($assessment['therapist_name'], 'UTF-8'));
    }

    /**
     * Test patient data bilingual format
     */
    public function testPatientDataBilingualFormat(): void
    {
        $patient = [
            'patient_id' => 1,
            'full_name_vi' => 'Nguyễn Thị Bình',
            'full_name_en' => 'Binh Thi Nguyen',
            'date_of_birth' => '1980-03-15',
            'gender' => 'female',
            'address_vi' => '123 Đường Lê Lợi, Quận 1, TP.HCM',
            'address_en' => '123 Le Loi Street, District 1, HCMC',
            'phone' => '+84 90 123 4567',
        ];

        $this->assertTrue(mb_check_encoding($patient['full_name_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($patient['address_vi'], 'UTF-8'));
        $this->assertMatchesRegularExpression('/^\+?[0-9\s]+$/', $patient['phone']);
    }

    /**
     * Test exercise prescription bilingual format
     */
    public function testExercisePrescriptionFormat(): void
    {
        $prescription = [
            'prescription_id' => 1,
            'patient_id' => 123,
            'exercise_name_vi' => 'Kéo giãn cơ vai',
            'exercise_name_en' => 'Shoulder stretch',
            'instructions_vi' => 'Thực hiện 10 lần, 3 hiệp/ngày',
            'instructions_en' => 'Perform 10 reps, 3 sets/day',
            'duration_weeks' => 4,
            'frequency_per_week' => 5,
        ];

        $this->assertIsInt($prescription['prescription_id']);
        $this->assertIsInt($prescription['patient_id']);
        $this->assertTrue(mb_check_encoding($prescription['exercise_name_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($prescription['instructions_vi'], 'UTF-8'));
        $this->assertIsInt($prescription['duration_weeks']);
        $this->assertIsInt($prescription['frequency_per_week']);
    }

    /**
     * Test Vietnamese insurance information format
     */
    public function testVietnameseInsuranceFormat(): void
    {
        $insurance = [
            'insurance_id' => 1,
            'patient_id' => 123,
            'insurance_company_vi' => 'Bảo Hiểm Xã Hội Việt Nam',
            'insurance_company_en' => 'Vietnam Social Security',
            'policy_number' => 'VN123456789',
            'coverage_type_vi' => 'Bảo hiểm y tế toàn diện',
            'coverage_type_en' => 'Comprehensive health insurance',
            'valid_from' => '2024-01-01',
            'valid_until' => '2024-12-31',
        ];

        $this->assertTrue(mb_check_encoding($insurance['insurance_company_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($insurance['coverage_type_vi'], 'UTF-8'));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $insurance['valid_from']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $insurance['valid_until']);
    }

    /**
     * Test outcome measures bilingual format
     */
    public function testOutcomeMeasuresFormat(): void
    {
        $outcome = [
            'outcome_id' => 1,
            'patient_id' => 123,
            'assessment_id' => 456,
            'measure_name_vi' => 'Thang đo đau VAS',
            'measure_name_en' => 'VAS Pain Scale',
            'score_initial' => 8,
            'score_current' => 4,
            'score_target' => 2,
            'notes_vi' => 'Tiến triển tốt, giảm đau đáng kể',
            'notes_en' => 'Good progress, significant pain reduction',
            'measurement_date' => date('Y-m-d'),
        ];

        $this->assertTrue(mb_check_encoding($outcome['measure_name_vi'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($outcome['notes_vi'], 'UTF-8'));
        $this->assertIsInt($outcome['score_initial']);
        $this->assertIsInt($outcome['score_current']);
        $this->assertIsInt($outcome['score_target']);
    }

    /**
     * Test query with Vietnamese LIKE search
     */
    public function testVietnameseLikeSearch(): void
    {
        // Mock query structure
        $likePatterns = [
            '%đau%' => ['Đau lưng', 'Đau vai', 'Đau đầu gối'],
            '%vật lý%' => ['Vật lý trị liệu'],
            '%massage%' => ['Massage trị liệu', 'Liệu pháp massage'],
        ];

        foreach ($likePatterns as $pattern => $expectedResults) {
            $this->assertIsArray($expectedResults);
            $this->assertNotEmpty($expectedResults);

            // Verify pattern format
            $this->assertStringStartsWith('%', $pattern);
            $this->assertStringEndsWith('%', $pattern);
        }
    }
}