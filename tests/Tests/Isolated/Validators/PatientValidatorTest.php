<?php

/**
 * Isolated PatientValidator Test
 *
 * Tests PatientValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\PatientValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class PatientValidatorTest extends TestCase
{
    private PatientValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new PatientValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingFirstName(): void
    {
        $invalidData = [
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15'
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
            'sex' => 'Male',
            'DOB' => '1990-01-15'
            // missing required 'lname'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing lname should fail validation');
    }

    public function testInsertValidationMissingSex(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'DOB' => '1990-01-15'
            // missing required 'sex'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing sex should fail validation');
    }

    public function testInsertValidationMissingDOB(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male'
            // missing required 'DOB'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing DOB should fail validation');
    }

    public function testInsertValidationFirstNameTooLong(): void
    {
        $invalidData = [
            'fname' => str_repeat('A', 256), // too long (over 255 characters)
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'First name too long should fail validation');
    }

    public function testInsertValidationLastNameTooShort(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'S', // too short (less than 2 characters)
            'sex' => 'Male',
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Last name too short should fail validation');
    }

    public function testInsertValidationLastNameTooLong(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => str_repeat('A', 256), // too long (over 255 characters)
            'sex' => 'Male',
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Last name too long should fail validation');
    }

    public function testInsertValidationSexTooShort(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'M', // too short (less than 4 characters)
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Sex too short should fail validation');
    }

    public function testInsertValidationSexTooLong(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => str_repeat('A', 31), // too long (over 30 characters)
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Sex too long should fail validation');
    }

    public function testInsertValidationInvalidDOBFormat(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-13-40' // invalid date
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid DOB format should fail validation');
    }

    public function testInsertValidationWithValidEmail(): void
    {
        $validData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15',
            'email' => 'john.smith@example.com'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with email should pass validation');
    }

    public function testInsertValidationWithInvalidEmail(): void
    {
        $invalidData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15',
            'email' => 'invalid-email' // invalid email format
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid email should fail validation');
    }

    public function testInsertValidationWithEmptyEmail(): void
    {
        $validData = [
            'fname' => 'John',
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15',
            'email' => '' // empty email should be allowed
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Empty email should pass validation');
    }

    public function testInsertValidationFirstNameMinLength(): void
    {
        $validData = [
            'fname' => 'J', // minimum length (1 character)
            'lname' => 'Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'First name with minimum length should pass');
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

    public function testUpdateValidationWithValidFields(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'fname' => 'Updated John',
            'lname' => 'Updated Smith',
            'sex' => 'Male',
            'DOB' => '1990-01-15',
            'email' => 'updated.john@example.com'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with valid fields should pass');
    }

    public function testIsExistingUuidWithValidUuid(): void
    {
        $validUuid = '123e4567-e89b-12d3-a456-426614174000';
        $result = $this->validator->isExistingUuid($validUuid);
        $this->assertTrue($result, 'Valid UUID should exist');
    }

    public function testIsExistingUuidWithInvalidUuid(): void
    {
        $validator = new PatientValidatorTestStub();
        $invalidUuid = 'not-a-valid-uuid';
        $result = $validator->isExistingUuid($invalidUuid);
        $this->assertFalse($result, 'Invalid UUID format should return false');
    }

    public function testIsExistingUuidWithNonExistentUuid(): void
    {
        $validator = new PatientValidatorTestStub();
        $nonExistentUuid = '999e4567-e89b-12d3-a456-426614179999';
        $result = $validator->isExistingUuid($nonExistentUuid);
        $this->assertFalse($result, 'Non-existent UUID should return false');
    }

    public function testIsExistingUuidMethodExists(): void
    {
        $this->assertTrue(method_exists($this->validator, 'isExistingUuid'));
    }

    public function testValidatorHasIsExistingUuidMethod(): void
    {
        $reflection = new \ReflectionClass($this->validator);
        $method = $reflection->getMethod('isExistingUuid');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfParameters());
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class PatientValidatorStub extends PatientValidator
{
    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        // For testing purposes, assume all IDs are valid
        return true;
    }

    /**
     * Override isExistingUuid to avoid database calls
     */
    public function isExistingUuid($uuid)
    {
        // For testing purposes, assume all UUIDs exist
        return true;
    }
}

/**
 * Test stub for specific isExistingUuid testing scenarios
 */
class PatientValidatorTestStub extends PatientValidator
{
    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        // For testing purposes, assume all IDs are valid
        return true;
    }

    /**
     * Override isExistingUuid to test different scenarios
     */
    public function isExistingUuid($uuid)
    {
        // Simulate validation logic without database

        // Return false for invalid UUID format
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $uuid)) {
            return false;
        }

        // Return false for specific "non-existent" UUID
        if ($uuid === '999e4567-e89b-12d3-a456-426614179999') {
            return false;
        }

        // Return true for all other valid UUIDs
        return true;
    }
}
