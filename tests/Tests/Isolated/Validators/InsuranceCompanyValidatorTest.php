<?php

/**
 * Isolated InsuranceCompanyValidator Test
 *
 * Tests InsuranceCompanyValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\InsuranceCompanyValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyValidatorTest extends TestCase
{
    private InsuranceCompanyValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new InsuranceCompanyValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'name' => 'Blue Cross Blue Shield'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingName(): void
    {
        $invalidData = [
            'cms_id' => '12345'
            // missing required 'name'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing name should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationNameTooShort(): void
    {
        $invalidData = [
            'name' => 'A' // too short (less than 2 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Name too short should fail validation');
    }

    public function testInsertValidationNameTooLong(): void
    {
        $invalidData = [
            'name' => str_repeat('A', 256) // too long (over 255 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Name too long should fail validation');
    }

    public function testInsertValidationWithAllOptionalFields(): void
    {
        $validData = [
            'name' => 'Blue Cross Blue Shield',
            'attn' => 'Claims Department',
            'cms_id' => '12345',
            'alt_cms_id' => 'ALT123',
            'ins_type_code' => 1,
            'x12_receiver_id' => 'RECEIVER123',
            'x12_default_partner_id' => 456
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with all optional fields should pass');
    }

    public function testInsertValidationAttnTooShort(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'attn' => 'A' // too short (less than 2 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Attn too short should fail validation');
    }

    public function testInsertValidationAttnTooLong(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'attn' => str_repeat('A', 256) // too long (over 255 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Attn too long should fail validation');
    }

    public function testInsertValidationCmsIdTooShort(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'cms_id' => '1' // too short (less than 2 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'CMS ID too short should fail validation');
    }

    public function testInsertValidationCmsIdTooLong(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'cms_id' => '1234567890123456' // too long (over 15 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'CMS ID too long should fail validation');
    }

    public function testInsertValidationAltCmsIdTooLong(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'alt_cms_id' => '1234567890123456' // too long (over 15 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Alt CMS ID too long should fail validation');
    }

    public function testInsertValidationInsTypeCodeNonNumeric(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'ins_type_code' => 'ABC' // non-numeric
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Non-numeric ins_type_code should fail validation');
    }

    public function testInsertValidationX12ReceiverIdTooShort(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'x12_receiver_id' => 'A' // too short (less than 2 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'X12 receiver ID too short should fail validation');
    }

    public function testInsertValidationX12ReceiverIdTooLong(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'x12_receiver_id' => str_repeat('A', 26) // too long (over 25 characters)
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'X12 receiver ID too long should fail validation');
    }

    public function testInsertValidationX12DefaultPartnerIdNonNumeric(): void
    {
        $invalidData = [
            'name' => 'Blue Cross Blue Shield',
            'x12_default_partner_id' => 'ABC' // non-numeric
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Non-numeric x12_default_partner_id should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'name' => 'Updated Insurance Company'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $invalidData = [
            'name' => 'Updated Insurance Company'
            // missing required 'uuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
    }

    public function testUpdateValidationInvalidUuidFormat(): void
    {
        $invalidData = [
            'uuid' => 'not-a-valid-uuid',
            'name' => 'Updated Insurance Company'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid UUID format should fail validation');
    }

    public function testUpdateValidationWithOptionalFields(): void
    {
        $updateData = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'name' => 'Updated Insurance Company',
            'attn' => 'Updated Claims Dept',
            'cms_id' => '54321',
            'ins_type_code' => 2
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with optional fields should pass');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class InsuranceCompanyValidatorStub extends InsuranceCompanyValidator
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
