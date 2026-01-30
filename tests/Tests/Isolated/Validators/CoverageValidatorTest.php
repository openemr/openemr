<?php

/**
 * Isolated CoverageValidator Test
 *
 * Tests CoverageValidator functionality without database dependencies.
 * CoverageValidator handles insurance coverage record validation with
 * complex business logic for subscriber relationships and policy conflicts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\CoverageValidator;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

class CoverageValidatorTest extends TestCase
{
    private CoverageValidatorStub $validator;

    protected function setUp(): void
    {
        $this->validator = new CoverageValidatorStub();

        // Set up global variables that the validator expects
        $GLOBALS['insurance_only_one'] = false;
        $GLOBALS['state_list'] = 'state';
        $GLOBALS['country_list'] = 'country';
    }

    public function testValidatorInheritsFromBaseValidator(): void
    {
        $this->assertInstanceOf(BaseValidator::class, $this->validator);
    }

    public function testValidatorIsInstantiable(): void
    {
        $validator = new CoverageValidatorStub();
        $this->assertInstanceOf(CoverageValidator::class, $validator);
    }

    public function testValidatorSupportsAllContexts(): void
    {
        // Test that all expected contexts are supported
        $this->assertTrue($this->validator->isValidContextPublic(BaseValidator::DATABASE_INSERT_CONTEXT));
        $this->assertTrue($this->validator->isValidContextPublic(BaseValidator::DATABASE_UPDATE_CONTEXT));
        $this->assertTrue($this->validator->isValidContextPublic(CoverageValidator::DATABASE_SWAP_CONTEXT));
    }

    public function testValidatorConstants(): void
    {
        $this->assertEquals('database-swap', CoverageValidator::DATABASE_SWAP_CONTEXT);
    }

    public function testGetInnerValidatorReturnsOpenEMRParticleValidator(): void
    {
        $innerValidator = $this->validator->getInnerValidatorPublic();
        $this->assertInstanceOf(\OpenEMR\Validators\OpenEMRParticleValidator::class, $innerValidator);
    }

    public function testInsertValidationWithValidData(): void
    {
        $validData = [
            'pid' => 123,
            'type' => 'primary',
            'provider' => 456,
            'policy_number' => 'POL123456',
            'subscriber_lname' => 'Doe',
            'subscriber_fname' => 'John',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Main St',
            'subscriber_postal_code' => '12345',
            'subscriber_city' => 'Anytown',
            'subscriber_state' => 'CA',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2024-01-01',
            'subscriber_ss' => '123-45-6789'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);

        // Basic validation should pass for our stub
        if (!$result->isValid()) {
            $errors = $result->getValidationMessages();
            $this->fail('Validation failed with errors: ' . print_r($errors, true));
        }
        $this->assertTrue($result->isValid());
    }

    public function testInsertValidationWithMissingRequiredFields(): void
    {
        $invalidData = [
            'pid' => 123,
            // Missing required fields like 'type', 'provider', etc.
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getValidationMessages());
    }

    public function testInsertValidationWithInvalidType(): void
    {
        $invalidData = [
            'pid' => 123,
            'type' => 'invalid_type', // Should be primary, secondary, or tertiary
            'provider' => 456,
            'policy_number' => 'POL123456',
            'subscriber_lname' => 'Doe',
            'subscriber_fname' => 'John',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Main St',
            'subscriber_postal_code' => '12345',
            'subscriber_city' => 'Anytown',
            'subscriber_state' => 'CA',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2024-01-01'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testInsertValidationWithSecondaryTypeWhenOnlyPrimaryAllowed(): void
    {
        // Set global to only allow primary insurance
        $GLOBALS['insurance_only_one'] = true;

        $invalidData = [
            'pid' => 123,
            'type' => 'secondary', // Should fail when insurance_only_one is true
            'provider' => 456,
            'policy_number' => 'POL123456',
            'subscriber_lname' => 'Doe',
            'subscriber_fname' => 'John',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Main St',
            'subscriber_postal_code' => '12345',
            'subscriber_city' => 'Anytown',
            'subscriber_state' => 'CA',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2024-01-01'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());

        // Reset global
        $GLOBALS['insurance_only_one'] = false;
    }

    public function testUpdateValidationWithValidUuid(): void
    {
        $validData = [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'type' => 'primary',
            'policy_number' => 'POL123456'
        ];

        $result = $this->validator->validate($validData, BaseValidator::DATABASE_UPDATE_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);

        // Should pass with valid UUID
        if (!$result->isValid()) {
            $errors = $result->getValidationMessages();
            $this->fail('Update validation failed with errors: ' . print_r($errors, true));
        }
        $this->assertTrue($result->isValid());
    }

    public function testUpdateValidationWithInvalidUuid(): void
    {
        $invalidData = [
            'uuid' => 'invalid-uuid-format',
            'type' => 'primary'
        ];

        $result = $this->validator->validate($invalidData, BaseValidator::DATABASE_UPDATE_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testSwapValidationWithValidData(): void
    {
        $validData = [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'pid' => 123,
            'type' => 'primary'
        ];

        $result = $this->validator->validate($validData, CoverageValidator::DATABASE_SWAP_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);

        // Should pass with valid data
        if (!$result->isValid()) {
            $errors = $result->getValidationMessages();
            $this->fail('Swap validation failed with errors: ' . print_r($errors, true));
        }
        $this->assertTrue($result->isValid());
    }

    public function testSwapValidationWithMissingRequiredFields(): void
    {
        $invalidData = [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            // Missing 'pid' and 'type'
        ];

        $result = $this->validator->validate($invalidData, CoverageValidator::DATABASE_SWAP_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testValidationWithOptionalFields(): void
    {
        $dataWithOptionals = [
            'pid' => 123,
            'type' => 'primary',
            'provider' => 456,
            'policy_number' => 'POL123456',
            'subscriber_lname' => 'Doe',
            'subscriber_fname' => 'John',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Main St',
            'subscriber_postal_code' => '12345',
            'subscriber_city' => 'Anytown',
            'subscriber_state' => 'CA',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2024-01-01',
            // Optional fields
            'plan_name' => 'Test Plan',
            'group_number' => 'GRP123',
            'subscriber_mname' => 'Middle',
            'subscriber_country' => 'US',
            'subscriber_phone' => '555-1234',
            'policy_type' => 'HMO',
            'subscriber_employer' => 'Test Corp',
            'copay' => '20.00',
            'date_end' => '2024-12-31'
        ];

        $result = $this->validator->validate($dataWithOptionals, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);

        if (!$result->isValid()) {
            $errors = $result->getValidationMessages();
            $this->fail('Validation with optionals failed: ' . print_r($errors, true));
        }
        $this->assertTrue($result->isValid());
    }

    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(CoverageValidator::class));
    }

    public function testValidatorRejectsUnsupportedContexts(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unsupported context: invalid-context');

        $this->validator->validate(['test' => 'data'], 'invalid-context');
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class CoverageValidatorStub extends CoverageValidator
{
    /**
     * Make isValidContext public for testing
     */
    public function isValidContextPublic($context)
    {
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('isValidContext');
        return $method->invoke($this, $context);
    }

    /**
     * Make getInnerValidator public for testing
     */
    public function getInnerValidatorPublic()
    {
        return $this->getInnerValidator();
    }

    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        if ($isUuid) {
            // Simple UUID format check
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $lookupId)) {
                $validationResult = new ProcessingResult();
                $validationMessages = [
                    $field => ["invalid or nonexisting value" => "value " . $lookupId],
                ];
                $validationResult->setValidationMessages($validationMessages);
                return $validationResult;
            }
            return true;
        } else {
            // Check if it's a valid integer
            if (!is_numeric($lookupId) || intval($lookupId) != $lookupId) {
                $validationResult = new ProcessingResult();
                $validationMessages = [
                    $field => ["invalid or nonexisting value" => "value " . $lookupId],
                ];
                $validationResult->setValidationMessages($validationMessages);
                return $validationResult;
            }
            return true;
        }
    }

    /**
     * Override configureValidator to avoid complex database-dependent callbacks
     */
    protected function configureValidator()
    {
        // Configure only the basic contexts without calling parent which has database calls
        array_push($this->supportedContexts, self::DATABASE_INSERT_CONTEXT, self::DATABASE_UPDATE_CONTEXT);
        array_push($this->supportedContexts, self::DATABASE_SWAP_CONTEXT);

        // Very simplified insert validations for testing - avoid all database calls
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function ($context): void {
                $context->required('pid')->numeric();
                $context->required('type')->inArray(['primary', 'secondary', 'tertiary'])
                    ->callback(function ($value) {
                        if ($GLOBALS['insurance_only_one']) {
                            if ($value !== 'primary') {
                                throw new \Particle\Validator\Exception\InvalidValueException("only primary insurance allowed with insurance_only_one global setting enabled", "INSURANCE_ONLY_ONE::INVALID_INSURANCE_TYPE");
                            }
                        }
                        return true;
                    });
                $context->required('provider')->numeric();
                $context->optional('plan_name')->lengthBetween(2, 255);
                $context->required('policy_number')->lengthBetween(2, 255);
                $context->optional('group_number')->lengthBetween(2, 255);
                $context->required('subscriber_lname')->lengthBetween(2, 255);
                $context->optional('subscriber_mname')->lengthBetween(1, 255);
                $context->required('subscriber_fname')->lengthBetween(1, 255);
                // Simplified - just check it's not empty
                $context->required('subscriber_relationship');
                $context->required('subscriber_DOB')->datetime('Y-m-d');
                $context->required('subscriber_street')->lengthBetween(2, 255);
                $context->required('subscriber_postal_code')->lengthBetween(2, 255);
                $context->required('subscriber_city')->lengthBetween(2, 255);
                // Simplified - just check it's not empty
                $context->required('subscriber_state');
                $context->optional('subscriber_country');
                $context->optional('subscriber_phone')->lengthBetween(2, 255);
                // Simplified - just check it's not empty
                $context->required('subscriber_sex');
                $context->required('accept_assignment')->inArray(['TRUE', 'FALSE']);
                $context->optional('policy_type')->allowEmpty(true);
                $context->optional('subscriber_employer')->lengthBetween(2, 255);
                $context->optional('copay');
                $context->required('date')->datetime('Y-m-d');
                $context->optional('date_end')->datetime('Y-m-d');
            }
        );

        // Simplified update validations
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function ($context): void {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules): void {
                        foreach ($rules as $key => $chain) {
                            if ($key !== 'type') {
                                $chain->required(false);
                            }
                        }
                    }
                );
                $context->required("uuid", "Coverage UUID")->callback(fn($value) => static::validateId("uuid", "insurance_data", $value, true))->uuid();
            }
        );

        // Simplified swap validations
        $this->validator->context(
            self::DATABASE_SWAP_CONTEXT,
            function ($context): void {
                $context->required("uuid", "Coverage UUID")->callback(fn($value) => static::validateId("uuid", "insurance_data", $value, true))->uuid();
                $context->required("pid", "Patient ID")->numeric();
                $context->required("type", "Coverage Type")->inArray(['primary', 'secondary', 'tertiary']);
            }
        );
    }
}
