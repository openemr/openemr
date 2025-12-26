<?php

namespace OpenEMR\Tests\Validators\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\VietnamesePT\PTOutcomeMeasuresValidator;

/**
 * PT Outcome Measures Validator Tests
 *
 * Tests validation rules for Vietnamese physiotherapy outcome measurements.
 * Covers required field validation for outcome tracking and progress monitoring.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTOutcomeMeasureValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new PTOutcomeMeasuresValidator();
    }

    /**
     * Test that valid outcome measure data passes validation
     */
    public function testValidOutcomeMeasureDataInsert(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'Range of Motion - Lumbar Flexion',
            'measure_name_vi' => 'Phạm vi chuyển động - Duỗi thắt lưng',
            'measurement_date' => '2024-01-15',
            'measurement_value' => 65,
            'measurement_unit' => 'degrees'
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
            'measure_name' => 'Range of Motion',
            'measurement_date' => '2024-01-15'
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
            'measure_name' => 'Range of Motion',
            'measurement_date' => '2024-01-15'
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
            'measure_name' => 'Range of Motion',
            'measurement_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that missing measure_name on insert fails validation
     */
    public function testMissingMeasureNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'measurement_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('measure_name', $result->getValidationMessages());
    }

    /**
     * Test that empty measure_name on insert fails validation
     */
    public function testEmptyMeasureNameOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'measure_name' => '',
            'measurement_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('measure_name', $result->getValidationMessages());
    }

    /**
     * Test that missing measurement_date on insert fails validation
     */
    public function testMissingMeasurementDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'measure_name' => 'Range of Motion'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('measurement_date', $result->getValidationMessages());
    }

    /**
     * Test that empty measurement_date on insert fails validation
     */
    public function testEmptyMeasurementDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'measure_name' => 'Range of Motion',
            'measurement_date' => ''
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('measurement_date', $result->getValidationMessages());
    }

    /**
     * Test that required fields can be omitted on update
     */
    public function testOptionalFieldsOnUpdate(): void
    {
        $updateData = [
            'measurement_value' => 75,
            'measurement_unit' => 'degrees'
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
            'patient_id' => 123,
            'measure_name' => 'Numerical Pain Rating Scale',
            'measurement_date' => '2024-01-20'
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
        $this->assertArrayHasKey('measure_name', $result->getValidationMessages());
        $this->assertArrayHasKey('measurement_date', $result->getValidationMessages());
        $this->assertEquals(3, count($result->getValidationMessages()));
    }

    /**
     * Test with Vietnamese measure names
     */
    public function testVietnameseMeasureNames(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'Pain Level',
            'measure_name_vi' => 'Mức độ đau',
            'measurement_date' => '2024-01-15',
            'measurement_value' => 4
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with minimal required fields only
     */
    public function testMinimalRequiredFields(): void
    {
        $validData = [
            'patient_id' => 999,
            'measure_name' => 'ROM Test',
            'measurement_date' => '2024-01-01'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test update with all three required fields present does not fail
     */
    public function testUpdateWithAllFields(): void
    {
        $updateData = [
            'patient_id' => 1,
            'measure_name' => 'Updated Measure Name',
            'measurement_date' => '2024-02-01'
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test update with single field
     */
    public function testUpdateWithSingleField(): void
    {
        $updateData = [
            'measurement_value' => 80
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with measurement values and units
     */
    public function testWithMeasurementValuesAndUnits(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'Range of Motion - Knee Flexion',
            'measurement_date' => '2024-01-15',
            'measurement_value' => 120,
            'measurement_unit' => 'degrees',
            'reference_value' => 135,
            'improvement_percentage' => 15.5
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with multiple outcome measure types
     */
    public function testMultipleOutcomeMeasureTypes(): void
    {
        $measureTypes = [
            'Range of Motion',
            'Muscle Strength',
            'Pain Scale',
            'Functional Ability',
            'Balance Test',
            'Flexibility'
        ];

        foreach ($measureTypes as $measureType) {
            $validData = [
                'patient_id' => 1,
                'measure_name' => $measureType,
                'measurement_date' => '2024-01-15'
            ];

            $result = $this->validator->validate($validData, false);

            $this->assertTrue($result->isValid(), "Failed for measure type: $measureType");
        }
    }

    /**
     * Test with numeric patient_id as string
     */
    public function testPatientIdAsString(): void
    {
        $validData = [
            'patient_id' => '456',
            'measure_name' => 'ROM Test',
            'measurement_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with measurement_date as datetime
     */
    public function testMeasurementDateAsDateTime(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'ROM Test',
            'measurement_date' => '2024-01-15 10:30:00'
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

    /**
     * Test with Vietnamese measurement names and descriptions
     */
    public function testVietnameseFullData(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'Range of Motion - Hip Extension',
            'measure_name_vi' => 'Phạm vi chuyển động - Duỗi hông',
            'measurement_date' => '2024-01-15',
            'measurement_value' => 25,
            'measurement_unit' => 'degrees',
            'notes' => 'Improvement noted from baseline',
            'notes_vi' => 'Cải thiện được ghi nhận từ ban đầu'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test with different date formats
     */
    public function testDifferentDateFormats(): void
    {
        $dateFormats = [
            '2024-01-15',
            '2024-1-15',
            '01-15-2024',
            '2024/01/15',
            '2024-01-15 14:30:00'
        ];

        foreach ($dateFormats as $dateFormat) {
            $validData = [
                'patient_id' => 1,
                'measure_name' => 'ROM Test',
                'measurement_date' => $dateFormat
            ];

            $result = $this->validator->validate($validData, false);

            $this->assertTrue($result->isValid(), "Failed for date format: $dateFormat");
        }
    }

    /**
     * Test negative patient_id passes validation (not checked by validator)
     */
    public function testNegativePatientIdOnInsert(): void
    {
        $validData = [
            'patient_id' => -1,
            'measure_name' => 'ROM Test',
            'measurement_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($validData, false);

        // Negative patient_id is allowed as validator only checks if empty
        $this->assertTrue($result->isValid());
    }

    /**
     * Test with all optional fields
     */
    public function testAllOptionalFields(): void
    {
        $validData = [
            'patient_id' => 1,
            'measure_name' => 'Functional Ability Index',
            'measurement_date' => '2024-01-15',
            'measurement_value' => 78,
            'measurement_unit' => 'percentage',
            'reference_value' => 100,
            'improvement_percentage' => 22.0,
            'notes' => 'Patient showing good progress',
            'notes_vi' => 'Bệnh nhân tiến triển tốt',
            'assessor' => 'PT John Smith',
            'baseline_value' => 64,
            'target_value' => 90
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }
}
