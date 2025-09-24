<?php

/**
 * Isolated ImmunizationValidator Test
 *
 * Tests ImmunizationValidator validation logic without database dependencies.
 * Note: ImmunizationValidator currently only inherits from BaseValidator
 * without adding specific validation rules.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\ImmunizationValidator;
use OpenEMR\Validators\BaseValidator;
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

    public function testValidatorSupportsInsertContext(): void
    {
        // ImmunizationValidator now properly configures insert and update contexts
        // Test that insert context validation works without errors
        $result = $this->validator->validate(['test' => 'data'], BaseValidator::DATABASE_INSERT_CONTEXT);

        // Since no specific validations are defined, it should succeed
        $this->assertTrue($result->isValid());
    }

    public function testValidatorSupportsUpdateContext(): void
    {
        // Test that update context validation works without errors
        $result = $this->validator->validate(['test' => 'data'], BaseValidator::DATABASE_UPDATE_CONTEXT);

        // Since no specific validations are defined, it should succeed
        $this->assertTrue($result->isValid());
    }

    public function testValidatorClassExists(): void
    {
        // Basic test to ensure the class can be instantiated and exists
        $this->assertTrue(class_exists(ImmunizationValidator::class));
    }

    public function testValidatorHasConfigureValidatorMethod(): void
    {
        // Test that the configureValidator method exists (even though it's empty)
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
