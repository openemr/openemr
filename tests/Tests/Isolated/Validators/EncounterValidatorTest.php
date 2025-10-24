<?php

/**
 * Isolated EncounterValidator Test
 *
 * Tests EncounterValidator validation logic without database dependencies.
 * Uses test stub to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\EncounterValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\TestCase;

class EncounterValidatorTest extends TestCase
{
    private EncounterValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new EncounterValidatorStub();
    }

    public function testInsertValidationRequiredFields(): void
    {
        $validData = [
            'pc_catid' => '9',
            'class_code' => 'AMB',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');
        $this->assertEmpty($result->getValidationMessages(), 'No validation errors expected');
    }

    public function testInsertValidationMissingPcCatid(): void
    {
        $invalidData = [
            'class_code' => 'AMB',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
            // missing required 'pc_catid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing pc_catid should fail validation');
        $this->assertNotEmpty($result->getValidationMessages(), 'Should have validation errors');
    }

    public function testInsertValidationMissingClassCode(): void
    {
        $invalidData = [
            'pc_catid' => '9',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000'
            // missing required 'class_code'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing class_code should fail validation');
    }

    public function testInsertValidationMissingPatientUuid(): void
    {
        $invalidData = [
            'pc_catid' => '9',
            'class_code' => 'AMB'
            // missing required 'puuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Data missing puuid should fail validation');
    }

    public function testInsertValidationInvalidPatientUuidFormat(): void
    {
        $invalidData = [
            'pc_catid' => '9',
            'class_code' => 'AMB',
            'puuid' => 'not-a-valid-uuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid UUID format should fail validation');
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $updateData = [
            'euuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user' => 'admin',
            'group' => 'Default'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with minimal valid data should pass');
    }

    public function testUpdateValidationMissingEncounterUuid(): void
    {
        $invalidData = [
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user' => 'admin',
            'group' => 'Default'
            // missing required 'euuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing euuid should fail validation');
    }

    public function testUpdateValidationMissingPatientUuid(): void
    {
        $invalidData = [
            'euuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'user' => 'admin',
            'group' => 'Default'
            // missing required 'puuid'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing puuid should fail validation');
    }

    public function testUpdateValidationMissingUser(): void
    {
        $invalidData = [
            'euuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'group' => 'Default'
            // missing required 'user'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing user should fail validation');
    }

    public function testUpdateValidationMissingGroup(): void
    {
        $invalidData = [
            'euuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user' => 'admin'
            // missing required 'group'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing group should fail validation');
    }

    public function testUpdateValidationInvalidEncounterUuidFormat(): void
    {
        $invalidData = [
            'euuid' => 'not-a-valid-uuid',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user' => 'admin',
            'group' => 'Default'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid encounter UUID format should fail validation');
    }

    public function testUpdateValidationWithOptionalClassCode(): void
    {
        $updateData = [
            'euuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'puuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user' => 'admin',
            'group' => 'Default',
            'class_code' => 'IMP'
        ];

        $result = $this->validator->validate($updateData, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with optional class_code should pass');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class EncounterValidatorStub extends EncounterValidator
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
     * Override validateCode to avoid database calls
     */
    public function validateCode($code, $table, $valueset)
    {
        // For testing purposes, assume all codes are valid
        return true;
    }
}
