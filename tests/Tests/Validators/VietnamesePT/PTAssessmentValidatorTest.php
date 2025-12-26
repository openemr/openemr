<?php

namespace OpenEMR\Tests\Validators\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\VietnamesePT\PTAssessmentValidator;

/**
 * PT Assessment Validator Tests
 *
 * Tests validation rules for Vietnamese physiotherapy assessments.
 * Covers required field validation, pain level range, UTF-8 encoding, and status enumeration.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTAssessmentValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new PTAssessmentValidator();
    }

    /**
     * Test that valid assessment data passes validation
     */
    public function testValidAssessmentDataInsert(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 5,
            'chief_complaint_vi' => 'Đau lưng dưới',
            'chief_complaint_en' => 'Lower back pain',
            'status' => 'draft'
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
            'assessment_date' => '2024-01-15',
            'pain_level' => 5
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
            'assessment_date' => '2024-01-15'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
    }

    /**
     * Test that missing assessment_date on insert fails validation
     */
    public function testMissingAssessmentDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'pain_level' => 5
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('assessment_date', $result->getValidationMessages());
    }

    /**
     * Test that empty assessment_date on insert fails validation
     */
    public function testEmptyAssessmentDateOnInsert(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'assessment_date' => ''
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('assessment_date', $result->getValidationMessages());
    }

    /**
     * Test that required fields can be omitted on update
     */
    public function testOptionalFieldsOnUpdate(): void
    {
        $updateData = [
            'pain_level' => 3
        ];

        $result = $this->validator->validate($updateData, true);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }

    /**
     * Test pain_level at minimum boundary (0)
     */
    public function testPainLevelMinimumBoundary(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 0
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertArrayNotHasKey('pain_level', $result->getValidationMessages());
    }

    /**
     * Test pain_level at maximum boundary (10)
     */
    public function testPainLevelMaximumBoundary(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 10
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertArrayNotHasKey('pain_level', $result->getValidationMessages());
    }

    /**
     * Test pain_level below minimum fails validation
     */
    public function testPainLevelBelowMinimum(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => -1
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('pain_level', $result->getValidationMessages());
        $this->assertStringContainsString('0 and 10', $result->getValidationMessages()['pain_level']);
    }

    /**
     * Test pain_level above maximum fails validation
     */
    public function testPainLevelAboveMaximum(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 11
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('pain_level', $result->getValidationMessages());
        $this->assertStringContainsString('0 and 10', $result->getValidationMessages()['pain_level']);
    }

    /**
     * Test pain_level as non-numeric fails validation
     */
    public function testPainLevelNonNumeric(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 'high'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('pain_level', $result->getValidationMessages());
    }

    /**
     * Test pain_level as string numeric is valid
     */
    public function testPainLevelStringNumericValid(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => '5'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test valid Vietnamese characters in chief_complaint_vi
     */
    public function testVietnameseCharactersValid(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'chief_complaint_vi' => 'Đau lưng dưới, tê chân phải, mỏi cơ thắt lưng'
        ];

        $result = $this->validator->validate($validData, false);

        // Should not have validation error for chief_complaint_vi
        $this->assertArrayNotHasKey('chief_complaint_vi', $result->getValidationMessages());
    }

    /**
     * Test common Vietnamese medical terms
     */
    public function testVietnameseMedicalTerms(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'chief_complaint_vi' => 'Đau cơ vai, tê bàn tay, yếu cơ'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('chief_complaint_vi', $result->getValidationMessages());
    }

    /**
     * Test status enum with valid value 'draft'
     */
    public function testStatusEnumDraft(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'status' => 'draft'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('status', $result->getValidationMessages());
    }

    /**
     * Test status enum with valid value 'completed'
     */
    public function testStatusEnumCompleted(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'status' => 'completed'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('status', $result->getValidationMessages());
    }

    /**
     * Test status enum with valid value 'reviewed'
     */
    public function testStatusEnumReviewed(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'status' => 'reviewed'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('status', $result->getValidationMessages());
    }

    /**
     * Test status enum with valid value 'cancelled'
     */
    public function testStatusEnumCancelled(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'status' => 'cancelled'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('status', $result->getValidationMessages());
    }

    /**
     * Test status enum with invalid value fails validation
     */
    public function testStatusEnumInvalid(): void
    {
        $invalidData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'status' => 'invalid_status'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('status', $result->getValidationMessages());
        $this->assertStringContainsString('must be one of', $result->getValidationMessages()['status']);
    }

    /**
     * Test multiple validation errors at once
     */
    public function testMultipleValidationErrors(): void
    {
        $invalidData = [
            'pain_level' => 15,
            'status' => 'pending'
        ];

        $result = $this->validator->validate($invalidData, false);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient_id', $result->getValidationMessages());
        $this->assertArrayHasKey('assessment_date', $result->getValidationMessages());
        $this->assertArrayHasKey('pain_level', $result->getValidationMessages());
        $this->assertArrayHasKey('status', $result->getValidationMessages());
    }

    /**
     * Test that empty chief_complaint_vi does not trigger UTF-8 validation
     */
    public function testEmptyChiefComplaintViSkipsValidation(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'chief_complaint_vi' => ''
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertArrayNotHasKey('chief_complaint_vi', $result->getValidationMessages());
    }

    /**
     * Test pain_level float values within range
     */
    public function testPainLevelFloatValid(): void
    {
        $validData = [
            'patient_id' => 1,
            'assessment_date' => '2024-01-15',
            'pain_level' => 5.5
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
    }

    /**
     * Test all valid fields together
     */
    public function testAllValidFieldsTogether(): void
    {
        $validData = [
            'patient_id' => 123,
            'assessment_date' => '2024-01-15 14:30:00',
            'pain_level' => 7,
            'chief_complaint_vi' => 'Đau lưng dưới, tê chân phải',
            'chief_complaint_en' => 'Lower back pain with right leg numbness',
            'status' => 'completed',
            'notes' => 'Additional notes about assessment'
        ];

        $result = $this->validator->validate($validData, false);

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, count($result->getValidationMessages()));
    }
}
