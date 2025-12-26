<?php

namespace OpenEMR\Tests\Validators\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\VietnamesePT\PTExercisePrescriptionValidator;

/**
 * PT Exercise Prescription Validator Tests
 *
 * Tests validation rules for Vietnamese physiotherapy exercise prescriptions.
 * Covers required field validation, range validation for exercise parameters.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTExercisePrescriptionValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new PTExercisePrescriptionValidator();
    }

    /**
     * Test that valid exercise prescription data passes validation
     */
    public function testValidExercisePrescriptionDataInsert(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'exercise_name_vi' => 'Duỗi cơ thắt lưng',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'sets_prescribed' => 3,
            'reps_prescribed' => 10,
            'duration_minutes' => 30,
            'frequency_per_week' => 3
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
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith'
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
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that missing exercise_name on insert fails validation
     */
    public function testMissingExerciseNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('exercise_name', $result->getValidationMessages());
    }

    /**
     * Test that empty exercise_name on insert fails validation
     */
    public function testEmptyExerciseNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => '',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('exercise_name', $result->getValidationMessages());
    }

    /**
     * Test that missing start_date on insert fails validation
     */
    public function testMissingStartDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'prescribed_by' => 'Dr. Smith'
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
            'exercise_name' => 'Lumbar extension',
            'start_date' => '',
            'prescribed_by' => 'Dr. Smith'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('start_date', $result->getValidationMessages());
    }

    /**
     * Test that missing prescribed_by on insert fails validation
     */
    public function testMissingPrescribedByOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('prescribed_by', $result->getValidationMessages());
    }

    /**
     * Test that empty prescribed_by on insert fails validation
     */
    public function testEmptyPrescribedByOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => ''
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('prescribed_by', $result->getValidationMessages());
    }

    /**
     * Test that required fields can be omitted on update
     */
    public function testOptionalFieldsOnUpdate(): void
    {
        $updateData = [
            'sets_prescribed' => 4,
            'reps_prescribed' => 12
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test sets_prescribed with zero value passes validation (0 is not < 0)
     */
    public function testSetsPrescribedZero(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'sets_prescribed' => 0
        ];

        $result = $this->validator->validate($validData, false);

        // Zero is allowed as the check is only for < 0
        $this->assertTrue($result->isValid());
    }

    /**
     * Test sets_prescribed with negative value fails validation
     */
    public function testSetsPrescribedNegative(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'sets_prescribed' => -5
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('sets_prescribed', $result->getValidationMessages());
    }

    /**
     * Test sets_prescribed with positive value passes
     */
    public function testSetsPrescribedPositive(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'sets_prescribed' => 5
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('sets_prescribed', $result->getValidationMessages());
    }

    /**
     * Test duration_minutes with zero value passes validation (0 is not < 0)
     */
    public function testDurationMinutesZero(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'duration_minutes' => 0
        ];

        $result = $this->validator->validate($validData, false);

        // Zero is allowed as the check is only for < 0
        $this->assertTrue($result->isValid());
    }

    /**
     * Test duration_minutes with negative value fails validation
     */
    public function testDurationMinutesNegative(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'duration_minutes' => -30
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('duration_minutes', $result->getValidationMessages());
    }

    /**
     * Test duration_minutes with positive value passes
     */
    public function testDurationMinutesPositive(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'duration_minutes' => 45
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('duration_minutes', $result->getValidationMessages());
    }

    /**
     * Test frequency_per_week at minimum boundary (1)
     */
    public function testFrequencyPerWeekMinimumBoundary(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => 1
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('frequency_per_week', $result->getValidationMessages());
    }

    /**
     * Test frequency_per_week at maximum boundary (7)
     */
    public function testFrequencyPerWeekMaximumBoundary(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => 7
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('frequency_per_week', $result->getValidationMessages());
    }

    /**
     * Test frequency_per_week below minimum fails validation
     */
    public function testFrequencyPerWeekBelowMinimum(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => 0
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('frequency_per_week', $result->getValidationMessages());
        $this->assertStringContainsString('1 and 7', $result->getValidationMessages()['frequency_per_week']);
    }

    /**
     * Test frequency_per_week above maximum fails validation
     */
    public function testFrequencyPerWeekAboveMaximum(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => 8
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('frequency_per_week', $result->getValidationMessages());
        $this->assertStringContainsString('1 and 7', $result->getValidationMessages()['frequency_per_week']);
    }

    /**
     * Test frequency_per_week with negative value fails validation
     */
    public function testFrequencyPerWeekNegative(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => -3
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('frequency_per_week', $result->getValidationMessages());
    }

    /**
     * Test frequency_per_week with valid value in middle range
     */
    public function testFrequencyPerWeekMiddleRange(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => 4
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test multiple validation errors at once
     */
    public function testMultipleValidationErrors(): void
    {
        $invalidData = [
            'sets_prescribed' => -1,
            'duration_minutes' => -15,
            'frequency_per_week' => 10
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
        $this->assertArrayHasKey('exercise_name', $result->getValidationMessages());
        $this->assertArrayHasKey('start_date', $result->getValidationMessages());
        $this->assertArrayHasKey('prescribed_by', $result->getValidationMessages());
        $this->assertArrayHasKey('sets_prescribed', $result->getValidationMessages());
        $this->assertArrayHasKey('duration_minutes', $result->getValidationMessages());
        $this->assertArrayHasKey('frequency_per_week', $result->getValidationMessages());
    }

    /**
     * Test all valid fields together
     */
    public function testAllValidFieldsTogether(): void
    {
        $validData = [
            'patient_id' => 123,
            'exercise_name' => 'Lumbar extension with twist',
            'exercise_name_vi' => 'Duỗi cơ thắt lưng xoay',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-03-15',
            'prescribed_by' => 'Dr. Jennifer Smith',
            'sets_prescribed' => 3,
            'reps_prescribed' => 12,
            'duration_minutes' => 45,
            'frequency_per_week' => 3,
            'intensity_level' => 'moderate',
            'notes' => 'Perform slowly with proper form'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test that only frequency_per_week validation on update doesn't fail
     */
    public function testFrequencyPerWeekUpdateOnly(): void
    {
        $updateData = [
            'frequency_per_week' => 5
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test large positive values for sets and duration
     */
    public function testLargePositiveValues(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'sets_prescribed' => 100,
            'duration_minutes' => 999
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test frequency_per_week with string numeric value
     */
    public function testFrequencyPerWeekStringNumeric(): void
    {
        $validData = [
            'patient_id' => 1,
            'exercise_name' => 'Lumbar extension',
            'start_date' => '2024-01-15',
            'prescribed_by' => 'Dr. Smith',
            'frequency_per_week' => '3'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }
}
