<?php

/**
 * Isolated AllergyIntoleranceValidator Test
 *
 * Tests AllergyIntoleranceValidator validation logic without database dependencies.
 * Uses mocking to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\AllergyIntoleranceValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class AllergyIntoleranceValidatorTest extends TestCase
{
    private AllergyIntoleranceValidatorStub $validator;

    protected function setUp(): void
    {
        // Use a test stub that doesn't hit the database
        $this->validator = new AllergyIntoleranceValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'title' => 'Penicillin Allergy',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingTitle(): void
    {
        $invalidData = [
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
            // missing required 'title'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing title should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationTitleTooShort(): void
    {
        $invalidData = [
            'title' => 'A', // too short (less than 2 characters)
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Title too short should fail validation');
    }

    public function testInsertValidationTitleTooLong(): void
    {
        $invalidData = [
            'title' => str_repeat('A', 256), // too long (over 255 characters)
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Title too long should fail validation');
    }

    public function testInsertValidationMissingPatientUuid(): void
    {
        $invalidData = [
            'title' => 'Penicillin Allergy'
            // missing required 'puuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing puuid should fail validation');
    }

    public function testInsertValidationWithOptionalFields(): void
    {
        $validData = [
            'title' => 'Penicillin Allergy',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'begdate' => '2023-01-01 10:00:00',
            'diagnosis' => 'Drug allergy',
            'enddate' => '2023-12-31 23:59:59',
            'comments' => 'Patient reported mild rash'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with optional fields should pass');
    }

    public function testInsertValidationInvalidDateFormat(): void
    {
        $invalidData = [
            'title' => 'Penicillin Allergy',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'begdate' => '2023-13-40 25:70:80' // invalid date format
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid date format should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        // For updates, all fields except uuid should be optional
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'title' => 'Updated Penicillin Allergy'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $invalidData = [
            'title' => 'Updated Penicillin Allergy'
            // missing required 'uuid' for update context
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class AllergyIntoleranceValidatorStub extends AllergyIntoleranceValidator
{
    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        // For testing purposes, assume all IDs are valid
        // In real tests, you could add logic to simulate different scenarios
        return true;
    }
}
