<?php

/**
 * Isolated ImmunizationValidator Test
 *
 * Tests ImmunizationValidator validation logic without database dependencies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ImmunizationValidator;
use PHPUnit\Framework\TestCase;

class ImmunizationValidatorTest extends TestCase
{
    private ImmunizationValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new ImmunizationValidatorStub();
    }

    public function testValidatorInheritsFromBaseValidator(): void
    {
        $this->assertInstanceOf(BaseValidator::class, $this->validator);
    }

    public function testValidatorIsInstantiable(): void
    {
        $validator = new ImmunizationValidatorStub();
        $this->assertInstanceOf(ImmunizationValidator::class, $validator);
    }

    public function testInsertRequiresPatientId(): void
    {
        $result = $this->validator->validate(
            ['cvx_code' => '197', 'administered_date' => '2024-01-15'],
            BaseValidator::DATABASE_INSERT_CONTEXT
        );
        $this->assertFalse($result->isValid());
    }

    public function testInsertRequiresCvxCode(): void
    {
        $result = $this->validator->validate(
            ['patient_id' => 1, 'administered_date' => '2024-01-15'],
            BaseValidator::DATABASE_INSERT_CONTEXT
        );
        $this->assertFalse($result->isValid());
    }

    public function testInsertRequiresAdministeredDate(): void
    {
        $result = $this->validator->validate(
            ['patient_id' => 1, 'cvx_code' => '197'],
            BaseValidator::DATABASE_INSERT_CONTEXT
        );
        $this->assertFalse($result->isValid());
    }

    public function testInsertAcceptsValidData(): void
    {
        $result = $this->validator->validate(
            ['patient_id' => 1, 'cvx_code' => '197', 'administered_date' => '2024-01-15'],
            BaseValidator::DATABASE_INSERT_CONTEXT
        );
        $this->assertTrue($result->isValid());
    }

    public function testUpdateRequiresUuid(): void
    {
        $result = $this->validator->validate(
            ['cvx_code' => '197'],
            BaseValidator::DATABASE_UPDATE_CONTEXT
        );
        $this->assertFalse($result->isValid());
    }

    public function testValidatorClassExists(): void
    {
        $this->assertTrue(class_exists(ImmunizationValidator::class));
    }

    public function testValidatorHasConfigureValidatorMethod(): void
    {
        $this->assertTrue(method_exists($this->validator, 'configureValidator'));
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class ImmunizationValidatorStub extends ImmunizationValidator
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
