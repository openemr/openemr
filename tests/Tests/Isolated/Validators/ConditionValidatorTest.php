<?php

/**
 * Isolated ConditionValidator Test
 *
 * Tests ConditionValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\ConditionValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class ConditionValidatorTest extends TestCase
{
    private ConditionValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new ConditionValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'title' => 'Hypertension',
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingTitle(): void
    {
        $invalidData = [
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
            // missing required 'title'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing title should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationMissingBeginDate(): void
    {
        $invalidData = [
            'title' => 'Hypertension',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
            // missing required 'begdate'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing begdate should fail validation');
    }

    public function testInsertValidationMissingPatientUuid(): void
    {
        $invalidData = [
            'title' => 'Hypertension',
            'begdate' => '2023-01-15'
            // missing required 'puuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing puuid should fail validation');
    }

    public function testInsertValidationTitleTooShort(): void
    {
        $invalidData = [
            'title' => 'H', // too short (less than 2 characters)
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Title too short should fail validation');
    }

    public function testInsertValidationTitleTooLong(): void
    {
        $invalidData = [
            'title' => str_repeat('A', 256), // too long (over 255 characters)
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Title too long should fail validation');
    }

    public function testInsertValidationInvalidDateFormat(): void
    {
        $invalidData = [
            'title' => 'Hypertension',
            'begdate' => '2023-13-40', // invalid date
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid date format should fail validation');
    }

    public function testInsertValidationWithOptionalFields(): void
    {
        $validData = [
            'title' => 'Hypertension',
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'diagnosis' => 'Essential hypertension',
            'enddate' => '2023-12-31'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with optional fields should pass');
    }

    public function testInsertValidationInvalidOptionalEndDate(): void
    {
        $invalidData = [
            'title' => 'Hypertension',
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'enddate' => 'invalid-date'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid optional enddate should fail validation');
    }

    public function testInsertValidationDiagnosisTooShort(): void
    {
        $invalidData = [
            'title' => 'Hypertension',
            'begdate' => '2023-01-15',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'diagnosis' => 'H' // too short
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Diagnosis too short should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'title' => 'Updated Hypertension'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $invalidData = [
            'title' => 'Updated Hypertension'
            // missing required 'uuid' for update context
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
    }

    public function testUpdateValidationInvalidUuidFormat(): void
    {
        $invalidData = [
            'uuid' => 'not-a-valid-uuid',
            'title' => 'Updated Hypertension'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid UUID format should fail validation');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class ConditionValidatorStub extends ConditionValidator
{
    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        // For testing purposes, assume all IDs are valid
        return true;
    }
}
