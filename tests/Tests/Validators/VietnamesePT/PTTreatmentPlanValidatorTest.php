<?php

namespace OpenEMR\Tests\Validators\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\VietnamesePT\PTTreatmentPlanValidator;

/**
 * PT Treatment Plan Validator Tests
 *
 * Tests validation rules for Vietnamese physiotherapy treatment plans.
 * Covers required field validation for treatment plan creation and updates.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTTreatmentPlanValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new PTTreatmentPlanValidator();
    }

    /**
     * Test that valid treatment plan data passes validation
     */
    public function testValidTreatmentPlanDataInsert(): void
    {
        $validData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'plan_name_vi' => 'Kế hoạch điều trị đau lưng dưới',
            'diagnosis_primary' => 'Lumbar strain',
            'diagnosis_primary_vi' => 'Căng cơ thắt lưng',
            'start_date' => '2024-01-15',
            'duration_weeks' => 12
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test that missing patient_id on insert fails validation
     */
    public function testMissingPatientIdOnInsert(): void
    {
        $invalidData = [
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that empty patient_id on insert fails validation
     */
    public function testEmptyPatientIdOnInsert(): void
    {
        $invalidData = [
            'patient_id' => '',
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that zero patient_id on insert fails validation
     */
    public function testZeroPatientIdOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 0,
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that missing plan_name on insert fails validation
     */
    public function testMissingPlanNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('plan_name', $result->getValidationMessages());
    }

    /**
     * Test that empty plan_name on insert fails validation
     */
    public function testEmptyPlanNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'plan_name' => '',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('plan_name', $result->getValidationMessages());
    }

    /**
     * Test that missing diagnosis_primary on insert fails validation
     */
    public function testMissingDiagnosisPrimaryOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('diagnosis_primary', $result->getValidationMessages());
    }

    /**
     * Test that empty diagnosis_primary on insert fails validation
     */
    public function testEmptyDiagnosisPrimaryOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => '',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('diagnosis_primary', $result->getValidationMessages());
    }

    /**
     * Test that missing start_date on insert fails validation
     */
    public function testMissingStartDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('start_date', $result->getValidationMessages());
    }

    /**
     * Test that empty start_date on insert fails validation
     */
    public function testEmptyStartDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => ''
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('start_date', $result->getValidationMessages());
    }

    /**
     * Test that required fields can be omitted on update
     */
    public function testOptionalFieldsOnUpdate(): void
    {
        $updateData = [
            'duration_weeks' => 8,
            'goals' => 'Improve mobility and reduce pain'
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test all required fields are validated together
     */
    public function testAllRequiredFieldsTogether(): void
    {
        $validData = [
            'patient_id' => 42,
            'plan_name' => 'Comprehensive Shoulder Rehabilitation',
            'diagnosis_primary' => 'Rotator cuff tendinitis',
            'start_date' => '2024-02-01'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test multiple missing required fields
     */
    public function testMultipleMissingRequiredFields(): void
    {
        $invalidData = [];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
        $this->assertArrayHasKey('plan_name', $result->getValidationMessages());
        $this->assertArrayHasKey('diagnosis_primary', $result->getValidationMessages());
        $this->assertArrayHasKey('start_date', $result->getValidationMessages());
        $this->assertEquals(4, count($result->getValidationMessages()));
    }

    /**
     * Test with Vietnamese diagnosis names
     */
    public function testVietnameseDiagnosisNames(): void
    {
        $validData = [
            'patient_id' => 1,
            'plan_name' => 'Rehabilitation Plan',
            'plan_name_vi' => 'Kế hoạch phục hồi',
            'diagnosis_primary' => 'Cervical strain',
            'diagnosis_primary_vi' => 'Căng cơ cổ',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test with only required fields on insert
     */
    public function testMinimalRequiredFields(): void
    {
        $validData = [
            'patient_id' => 999,
            'plan_name' => 'PT Plan',
            'diagnosis_primary' => 'Knee pain',
            'start_date' => '2024-01-01'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test update with all four required fields present does not fail
     */
    public function testUpdateWithAllFields(): void
    {
        $updateData = [
            'patient_id' => 1,
            'plan_name' => 'Updated Plan Name',
            'diagnosis_primary' => 'Updated Diagnosis',
            'start_date' => '2024-03-01'
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test update with partial fields
     */
    public function testUpdateWithPartialFields(): void
    {
        $updateData = [
            'plan_name' => 'Updated Plan Name',
            'duration_weeks' => 16
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with all optional fields
     */
    public function testAllOptionalFields(): void
    {
        $validData = [
            'patient_id' => 1,
            'plan_name' => 'Lower Back Pain Treatment',
            'diagnosis_primary' => 'Lumbar strain',
            'start_date' => '2024-01-15',
            'diagnosis_secondary' => 'Lumbar stenosis',
            'diagnosis_secondary_vi' => 'Hẹp ống sống thắt lưng',
            'duration_weeks' => 12,
            'goals' => 'Restore function and reduce pain',
            'goals_vi' => 'Phục hồi chức năng và giảm đau',
            'precautions' => 'Avoid heavy lifting',
            'precautions_vi' => 'Tránh nâng vật nặng',
            'therapy_modalities' => 'Exercises, stretching, manual therapy',
            'expected_outcomes' => 'Full recovery within 12 weeks'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test with long plan names
     */
    public function testLongPlanName(): void
    {
        $longName = 'Comprehensive Rehabilitation Plan for Chronic Lower Back Pain with Multiple Comorbidities Including Osteoarthritis';

        $validData = [
            'patient_id' => 1,
            'plan_name' => $longName,
            'diagnosis_primary' => 'Chronic lumbar pain',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with numeric patient_id as string
     */
    public function testPatientIdAsString(): void
    {
        $validData = [
            'patient_id' => '123',
            'plan_name' => 'Treatment Plan',
            'diagnosis_primary' => 'Pain syndrome',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with start_date as datetime
     */
    public function testStartDateAsDateTime(): void
    {
        $validData = [
            'patient_id' => 1,
            'plan_name' => 'Treatment Plan',
            'diagnosis_primary' => 'Pain syndrome',
            'start_date' => '2024-01-15 14:30:00'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test update mode with no fields doesn't fail
     */
    public function testUpdateWithNoFields(): void
    {
        $updateData = [];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test insert mode is stricter than update mode
     */
    public function testInsertStricterThanUpdate(): void
    {
        $partialData = [
            'patient_id' => 1
        ];

        // Insert should fail
        $insertResult = $this->validator->validate($partialData, false);
        $this->assertFalse($insertResult->isValid());

        // Update should pass
        $updateResult = $this->validator->validate($partialData, true);
        $this->assertTrue($updateResult->isValid());
    }
}
