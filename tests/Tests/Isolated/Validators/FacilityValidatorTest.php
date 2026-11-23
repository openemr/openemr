<?php

/**
 * Isolated FacilityValidator Test
 *
 * Tests FacilityValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\FacilityValidator;
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

    public function testFormSaveValidFullSet(): void
    {
        $validData = [
            'name' => 'General Hospital',
            'phone' => '555-0123',
            'fax' => '555-0124',
            'street' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'country_code' => 'US',
            'federal_ein' => '12-3456789',
            'website' => 'https://hospital.com',
            'email' => 'info@hospital.com',
            'color' => '#FF0000',
            'service_location' => '1',
            'billing_location' => '0',
            'accepts_assignment' => '1',
            'pos_code' => '11',
            'domain_identifier' => '05D1234567',
            'attn' => 'Medical Records',
            'tax_id_type' => 'EI',
            'primary_business_entity' => '1',
            'facility_npi' => '1234567890',
            'facility_taxonomy' => '261QA1903X',
            'facility_code' => 'GH001',
            'mail_street' => '456 Oak Ave',
            'mail_street2' => 'Suite 100',
            'mail_city' => 'Springfield',
            'mail_state' => 'IL',
            'mail_zip' => '62701',
            'oid' => '1.2.3.4.5',
            'iban' => 'GB82WEST12345698765432',
            'info' => 'some notes',
            'inactive' => '0',
            'organization_type' => 'Clinic',
            'mammography_cert_number' => 'MAMMO12345678901234567890',
        ];

        $result = $this->validator->validate($validData, FacilityValidator::FORM_SAVE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid full set should pass form-save validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testFormSaveMissingNameRejected(): void
    {
        $invalidData = [
            'phone' => '555-0123',
            // missing required 'name'
        ];

        $result = $this->validator->validate($invalidData, FacilityValidator::FORM_SAVE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Missing name should fail form-save validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testFormSaveNameLengthBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result255 = $this->validator->validate(array_merge($base, ['name' => str_repeat('A', 255)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result255->isValid(), 'Name of 255 characters should pass');

        $result256 = $this->validator->validate(array_merge($base, ['name' => str_repeat('A', 256)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result256->isValid(), 'Name of 256 characters should fail');
    }

    public function testFormSavePhoneLengthBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result30 = $this->validator->validate(array_merge($base, ['phone' => str_repeat('9', 30)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result30->isValid(), 'Phone of 30 characters should pass');

        $result31 = $this->validator->validate(array_merge($base, ['phone' => str_repeat('9', 31)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result31->isValid(), 'Phone of 31 characters should fail');
    }

    public function testFormSavePostalCodeLengthBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result11 = $this->validator->validate(array_merge($base, ['postal_code' => str_repeat('9', 11)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result11->isValid(), 'Postal code of 11 characters should pass');

        $result12 = $this->validator->validate(array_merge($base, ['postal_code' => str_repeat('9', 12)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result12->isValid(), 'Postal code of 12 characters should fail');
    }

    public function testFormSaveIbanLengthBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result50 = $this->validator->validate(array_merge($base, ['iban' => str_repeat('A', 50)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result50->isValid(), 'IBAN of 50 characters should pass');

        $result51 = $this->validator->validate(array_merge($base, ['iban' => str_repeat('A', 51)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result51->isValid(), 'IBAN of 51 characters should fail');
    }

    public function testFormSaveDomainIdentifier(): void
    {
        $base = ['name' => 'General Hospital'];

        $resultAlnum = $this->validator->validate(array_merge($base, ['domain_identifier' => '05D1234567']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultAlnum->isValid(), 'Alphanumeric domain_identifier should pass');

        $result60 = $this->validator->validate(array_merge($base, ['domain_identifier' => str_repeat('A', 60)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result60->isValid(), '60-character domain_identifier should pass');

        $result61 = $this->validator->validate(array_merge($base, ['domain_identifier' => str_repeat('A', 61)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result61->isValid(), '61-character domain_identifier should fail');

        $resultNonAlnum = $this->validator->validate(array_merge($base, ['domain_identifier' => 'ABC-123']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultNonAlnum->isValid(), 'Non-alphanumeric domain_identifier should fail');
    }

    public function testFormSaveFacilityNpi(): void
    {
        $base = ['name' => 'General Hospital'];

        $resultEmpty = $this->validator->validate(array_merge($base, ['facility_npi' => '']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultEmpty->isValid(), 'Empty facility_npi should pass (optional)');

        $resultTooShort = $this->validator->validate(array_merge($base, ['facility_npi' => '12345']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultTooShort->isValid(), '5-digit facility_npi should fail');

        $resultTen = $this->validator->validate(array_merge($base, ['facility_npi' => '1234567890']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultTen->isValid(), '10-digit facility_npi should pass');
    }

    public function testFormSaveTaxIdType(): void
    {
        $base = ['name' => 'General Hospital'];

        $resultEI = $this->validator->validate(array_merge($base, ['tax_id_type' => 'EI']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultEI->isValid(), 'tax_id_type EI should pass');

        $resultXX = $this->validator->validate(array_merge($base, ['tax_id_type' => 'XX']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultXX->isValid(), 'tax_id_type XX should fail');
    }

    public function testFormSaveCheckboxBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];
        $checkboxes = ['service_location', 'billing_location', 'accepts_assignment', 'primary_business_entity', 'inactive'];

        foreach ($checkboxes as $field) {
            $resultOne = $this->validator->validate(array_merge($base, [$field => '1']), FacilityValidator::FORM_SAVE_CONTEXT);
            $this->assertTrue($resultOne->isValid(), "Checkbox $field value 1 should pass");

            $resultTwo = $this->validator->validate(array_merge($base, [$field => '2']), FacilityValidator::FORM_SAVE_CONTEXT);
            $this->assertFalse($resultTwo->isValid(), "Checkbox $field value 2 should fail");
        }
    }

    public function testFormSavePosCode(): void
    {
        $base = ['name' => 'General Hospital'];

        $result11 = $this->validator->validate(array_merge($base, ['pos_code' => '11']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result11->isValid(), 'pos_code 11 (Office) should pass');

        $result21 = $this->validator->validate(array_merge($base, ['pos_code' => '21']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result21->isValid(), 'pos_code 21 (Inpatient) should pass');

        $resultEmpty = $this->validator->validate(array_merge($base, ['pos_code' => '']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultEmpty->isValid(), 'Empty pos_code should pass (optional)');

        $resultAbc = $this->validator->validate(array_merge($base, ['pos_code' => 'abc']), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultAbc->isValid(), 'Non-numeric pos_code abc should fail');
    }

    public function testFormSaveNameMultibyteBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result255 = $this->validator->validate(array_merge($base, ['name' => str_repeat('é', 255)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result255->isValid(), 'Name of 255 multibyte characters should pass');

        $result256 = $this->validator->validate(array_merge($base, ['name' => str_repeat('é', 256)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result256->isValid(), 'Name of 256 multibyte characters should fail');
    }

    public function testFormSavePosCodeTinyintBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result127 = $this->validator->validate(array_merge($base, ['pos_code' => 127]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result127->isValid(), 'pos_code 127 (max signed tinyint) should pass');

        $resultNeg128 = $this->validator->validate(array_merge($base, ['pos_code' => -128]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($resultNeg128->isValid(), 'pos_code -128 (min signed tinyint) should pass');

        $result128 = $this->validator->validate(array_merge($base, ['pos_code' => 128]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result128->isValid(), 'pos_code 128 (over max signed tinyint) should fail');

        $resultNeg129 = $this->validator->validate(array_merge($base, ['pos_code' => -129]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultNeg129->isValid(), 'pos_code -129 (under min signed tinyint) should fail');

        $resultFloat = $this->validator->validate(array_merge($base, ['pos_code' => 1.5]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($resultFloat->isValid(), 'pos_code 1.5 (non-integer) should fail');
    }
    public function testFormSaveMammographyLengthBoundaries(): void
    {
        $base = ['name' => 'General Hospital'];

        $result30 = $this->validator->validate(array_merge($base, ['mammography_cert_number' => str_repeat('9', 30)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertTrue($result30->isValid(), 'Mammography cert number of 30 characters should pass');

        $result31 = $this->validator->validate(array_merge($base, ['mammography_cert_number' => str_repeat('9', 31)]), FacilityValidator::FORM_SAVE_CONTEXT);
        $this->assertFalse($result31->isValid(), 'Mammography cert number of 31 characters should fail');
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
    public static function validateId($field, $table, $lookupId, $isUuid = false): bool
    {
        // For testing purposes, assume all IDs are valid
        return true;
    }
}
