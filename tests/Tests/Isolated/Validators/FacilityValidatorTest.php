<?php

/**
 * Isolated FacilityValidator Test
 *
 * Tests FacilityValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\FacilityValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class FacilityValidatorTest extends TestCase
{
    private FacilityValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new FacilityValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingName(): void
    {
        $invalidData = [
            'facility_npi' => '1234567890'
            // missing required 'name'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing name should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationMissingFacilityNpi(): void
    {
        $invalidData = [
            'name' => 'General Hospital'
            // missing required 'facility_npi'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing facility_npi should fail validation');
    }

    public function testInsertValidationNameTooShort(): void
    {
        $invalidData = [
            'name' => 'H', // too short (less than 2 characters)
            'facility_npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Name too short should fail validation');
    }

    public function testInsertValidationNameTooLong(): void
    {
        $invalidData = [
            'name' => str_repeat('A', 256), // too long (over 255 characters)
            'facility_npi' => '1234567890'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Name too long should fail validation');
    }

    public function testInsertValidationFacilityNpiTooShort(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => '123456789' // too short (less than 10 digits)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Facility NPI too short should fail validation');
    }

    public function testInsertValidationFacilityNpiTooLong(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890123456' // too long (over 15 digits)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Facility NPI too long should fail validation');
    }

    public function testInsertValidationFacilityNpiNonNumeric(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => 'ABC1234567' // non-numeric
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Non-numeric facility NPI should fail validation');
    }

    public function testInsertValidationInvalidEmail(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890',
            'email' => 'invalid-email' // invalid email format
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid email should fail validation');
    }

    public function testInsertValidationInvalidWebsite(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890',
            'website' => 'not-a-url' // invalid URL format
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid website URL should fail validation');
    }

    public function testInsertValidationWithAllOptionalFields(): void
    {
        $validData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890',
            'domain_identifier' => 123,
            'phone' => '555-0123',
            'city' => 'Springfield',
            'state' => 'IL',
            'street' => '123 Main St',
            'postal_code' => '62701',
            'email' => 'info@hospital.com',
            'fax' => '555-0124',
            'country_code' => 'US',
            'federal_ein' => '12-3456789',
            'website' => 'https://hospital.com',
            'color' => '#FF0000',
            'service_location' => 1,
            'billing_location' => 1,
            'accepts_assignment' => 1,
            'pos_code' => 21,
            'attn' => 'Medical Records',
            'tax_id_type' => 'EIN',
            'primary_business_entity' => 1,
            'facility_code' => 'GH001',
            'facility_taxonomy' => '261QA1903X',
            'iban' => 'GB82WEST12345698765432'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with all optional fields should pass');
    }

    public function testInsertValidationOptionalFieldTooShort(): void
    {
        $invalidData = [
            'name' => 'General Hospital',
            'facility_npi' => '1234567890',
            'city' => 'A' // too short (less than 2 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Optional field too short should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'name' => 'Updated Hospital Name'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $invalidData = [
            'name' => 'Updated Hospital Name'
            // missing required 'uuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
    }

    public function testUpdateValidationInvalidUuidFormat(): void
    {
        $invalidData = [
            'uuid' => 'not-a-valid-uuid',
            'name' => 'Updated Hospital Name'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid UUID format should fail validation');
    }

    public function testUpdateValidationWithOptionalFields(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'name' => 'Updated Hospital',
            'facility_npi' => '9876543210',
            'email' => 'updated@hospital.com',
            'website' => 'https://updated-hospital.com'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with optional fields should pass');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class FacilityValidatorStub extends FacilityValidator
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
