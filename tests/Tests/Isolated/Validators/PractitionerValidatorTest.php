<?php

/**
 * Isolated PractitionerValidator Test
 *
 * Tests PractitionerValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\PractitionerValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class PractitionerValidatorTest extends TestCase
{
    private PractitionerValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new PractitionerValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '1234567890'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingFirstName(): void
    {
        $invalidData = [
            'lname' => 'Smith',
            'npi' => '1234567890'
            // missing required 'fname'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing fname should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationMissingLastName(): void
    {
        $invalidData = [
            'fname' => 'John',
            'npi' => '1234567890'
            // missing required 'lname'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing lname should fail validation');
    }

    public function testInsertValidationMissingNpi(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith'
            // missing required 'npi'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing npi should fail validation');
    }

    public function testInsertValidationFirstNameTooShort(): void
    {
        $invalidData = [
            'fname' => 'J', // too short (less than 2 characters)
            'lname' => 'Smith',
            'npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'First name too short should fail validation');
    }

    public function testInsertValidationFirstNameTooLong(): void
    {
        $invalidData = [
            'fname' => str_repeat('A', 256), // too long (over 255 characters)
            'lname' => 'Smith',
            'npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'First name too long should fail validation');
    }

    public function testInsertValidationLastNameTooShort(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'S', // too short (less than 2 characters)
            'npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Last name too short should fail validation');
    }

    public function testInsertValidationLastNameTooLong(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => str_repeat('A', 256), // too long (over 255 characters)
            'npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Last name too long should fail validation');
    }

    public function testInsertValidationNpiTooShort(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '123456789' // too short (less than 10 digits)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'NPI too short should fail validation');
    }

    public function testInsertValidationNpiTooLong(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '1234567890123456' // too long (over 15 digits)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'NPI too long should fail validation');
    }

    public function testInsertValidationNpiNonNumeric(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => 'ABC1234567' // non-numeric
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Non-numeric NPI should fail validation');
    }

    public function testInsertValidationWithValidOptionalFields(): void
    {
        $validData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '1234567890',
            'facility_id' => 123,
            'email' => 'john.smith@hospital.com'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with optional fields should pass');
    }

    public function testInsertValidationFacilityIdNonNumeric(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '1234567890',
            'facility_id' => 'ABC' // non-numeric
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Non-numeric facility_id should fail validation');
    }

    public function testInsertValidationInvalidEmail(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'npi' => '1234567890',
            'email' => 'invalid-email' // invalid email format
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid email should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'fname' => 'Updated John'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $invalidData = [
            'fname' => 'Updated John'
            // missing required 'uuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
    }

    public function testUpdateValidationInvalidUuidFormat(): void
    {
        $invalidData = [
            'uuid' => 'not-a-valid-uuid',
            'fname' => 'Updated John'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid UUID format should fail validation');
    }

    public function testUpdateValidationWithOptionalFields(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'fname' => 'Updated John',
            'lname' => 'Updated Smith',
            'npi' => '9876543210',
            'facility_id' => 456,
            'email' => 'updated.john@hospital.com'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with optional fields should pass');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class PractitionerValidatorStub extends PractitionerValidator
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
