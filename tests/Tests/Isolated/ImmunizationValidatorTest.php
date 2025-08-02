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

    public function testValidatorHasEmptyConfiguration(): void
    {
        // ImmunizationValidator currently has an empty configureValidator() method
        // This test documents the current state - it doesn't add any validation contexts

        // We can test this by trying to validate with unsupported contexts
        // The empty configuration causes internal errors in the validator
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function merge() on null');

        $this->validator->validate(['test' => 'data'], BaseValidator::DATABASE_INSERT_CONTEXT);
    }

    public function testValidatorRejectsUpdateContext(): void
    {
        // Similar test for update context
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function merge() on null');

        $this->validator->validate(['test' => 'data'], BaseValidator::DATABASE_UPDATE_CONTEXT);
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
