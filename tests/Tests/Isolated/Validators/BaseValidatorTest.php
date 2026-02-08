<?php

/**
 * Isolated BaseValidator Test
 *
 * Tests BaseValidator functionality without database dependencies.
 * BaseValidator is the abstract base class for all OpenEMR validators.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

class BaseValidatorTest extends TestCase
{
    private BaseValidatorTestStub $validator;

    protected function setUp(): void
    {
        $this->validator = new BaseValidatorTestStub();
    }

    public function testValidatorConstants(): void
    {
        $this->assertEquals('db-insert', BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertEquals('db-update', BaseValidator::DATABASE_UPDATE_CONTEXT);
    }

    public function testValidatorIsInstantiable(): void
    {
        $validator = new BaseValidatorTestStub();
        $this->assertInstanceOf(BaseValidator::class, $validator);
    }

    public function testValidatorConfiguresContextsOnConstruction(): void
    {
        // The stub should have both insert and update contexts supported
        $this->assertTrue($this->validator->isValidContextPublic(BaseValidator::DATABASE_INSERT_CONTEXT));
        $this->assertTrue($this->validator->isValidContextPublic(BaseValidator::DATABASE_UPDATE_CONTEXT));
    }

    public function testValidatorRejectsUnsupportedContext(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unsupported context: invalid-context');

        $this->validator->validate(['test' => 'data'], 'invalid-context');
    }

    public function testValidateReturnsProcessingResult(): void
    {
        $result = $this->validator->validate(['test' => 'data'], BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);
    }

    public function testValidateWithValidData(): void
    {
        $data = ['name' => 'Test Name'];
        $result = $this->validator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertInstanceOf(ProcessingResult::class, $result);

        // Debug output if validation fails
        if (!$result->isValid()) {
            $errors = $result->getValidationMessages();
            $this->fail('Validation failed with errors: ' . print_r($errors, true));
        }

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getValidationMessages());
    }

    public function testValidateWithInvalidData(): void
    {
        // Our stub validator requires 'name' field
        $data = []; // Missing required field
        $result = $this->validator->validate($data, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getValidationMessages());
    }

    public function testGetInnerValidator(): void
    {
        $innerValidator = $this->validator->getInnerValidatorPublic();
        $this->assertInstanceOf(\Particle\Validator\Validator::class, $innerValidator);
    }

    public function testGetInnerValidatorReturnsSameInstance(): void
    {
        $validator1 = $this->validator->getInnerValidatorPublic();
        $validator2 = $this->validator->getInnerValidatorPublic();

        $this->assertSame($validator1, $validator2);
    }

    public function testValidateIdStaticMethodWithValidInteger(): void
    {
        // Mock a database result by using our stub
        $result = BaseValidatorTestStub::validateId('id', 'test_table', 123);
        $this->assertTrue($result);
    }

    public function testValidateIdStaticMethodWithInvalidInteger(): void
    {
        $result = BaseValidatorTestStub::validateId('id', 'test_table', 999);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getValidationMessages());
    }

    public function testValidateIdStaticMethodWithInvalidType(): void
    {
        $result = BaseValidatorTestStub::validateId('id', 'test_table', 'invalid');
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testValidateIdStaticMethodWithUuid(): void
    {
        // Test with a valid UUID format
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $result = BaseValidatorTestStub::validateId('uuid', 'test_table', $validUuid, true);
        $this->assertTrue($result);
    }

    public function testValidateIdStaticMethodWithInvalidUuid(): void
    {
        $invalidUuid = 'not-a-uuid';
        $result = BaseValidatorTestStub::validateId('uuid', 'test_table', $invalidUuid, true);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testValidateCodeMethodWithValidCode(): void
    {
        $result = $this->validator->validateCodePublic('valid_code', 'list_options', 'test_list');
        $this->assertTrue($result);
    }

    public function testValidateCodeMethodWithInvalidCode(): void
    {
        $result = $this->validator->validateCodePublic('invalid_code', 'list_options', 'test_list');
        $this->assertFalse($result);
    }

    public function testValidateCodeMethodWithEmptyCode(): void
    {
        $result = $this->validator->validateCodePublic('', 'list_options', 'test_list');
        $this->assertFalse($result);
    }

    public function testValidateCodeMethodWithNullCode(): void
    {
        $result = $this->validator->validateCodePublic(null, 'list_options', 'test_list');
        $this->assertFalse($result);
    }

    public function testCustomValidatorWithDifferentContexts(): void
    {
        $customValidator = new BaseValidatorCustomStub();

        // This custom validator only supports insert context
        $this->assertTrue($customValidator->isValidContextPublic(BaseValidator::DATABASE_INSERT_CONTEXT));
        $this->assertFalse($customValidator->isValidContextPublic(BaseValidator::DATABASE_UPDATE_CONTEXT));

        // Should work with insert context
        $result = $customValidator->validate(['test' => 'data'], BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertInstanceOf(ProcessingResult::class, $result);

        // Should throw exception with update context
        $this->expectException(\RuntimeException::class);
        $customValidator->validate(['test' => 'data'], BaseValidator::DATABASE_UPDATE_CONTEXT);
    }
}

/**
 * Test stub that extends BaseValidator for testing purposes
 */
class BaseValidatorTestStub extends BaseValidator
{
    protected function getInnerValidator(): \Particle\Validator\Validator
    {
        if (empty($this->validator)) {
            $this->validator = new \OpenEMR\Validators\OpenEMRParticleValidator();
        }
        return $this->validator;
    }

    protected function configureValidator()
    {
        parent::configureValidator();

        // Configure some basic validation rules for testing
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function ($context): void {
                $context->required('name')->lengthBetween(1, 100);
            }
        );

        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function ($context): void {
                $context->required('name')->lengthBetween(1, 100);
            }
        );
    }

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
     * Make validateCode public for testing
     */
    public function validateCodePublic($code, $table, $valueset)
    {
        return $this->validateCode($code, $table, $valueset);
    }

    /**
     * Override validateId to avoid database calls
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        // Simulate validation logic without database
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

            // Simulate database lookup - return true for ID 123, false for others
            if (intval($lookupId) === 123) {
                return true;
            } else {
                $validationResult = new ProcessingResult();
                $validationMessages = [
                    $field => ["invalid or nonexisting value" => "value " . $lookupId],
                ];
                $validationResult->setValidationMessages($validationMessages);
                return $validationResult;
            }
        }
    }

    /**
     * Override validateCode to avoid database calls
     */
    public function validateCode($code, $table, $valueset)
    {
        // Simulate database lookup - return true for 'valid_code', false for others
        if ($code === 'valid_code') {
            return true;
        }
        return false;
    }
}

/**
 * Custom validator stub that only supports insert context
 */
class BaseValidatorCustomStub extends BaseValidator
{
    protected function getInnerValidator(): \Particle\Validator\Validator
    {
        return new \OpenEMR\Validators\OpenEMRParticleValidator();
    }

    protected function configureValidator()
    {
        // Only support insert context, not update
        array_push($this->supportedContexts, self::DATABASE_INSERT_CONTEXT);

        // Configure validation rule for insert context
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function ($context): void {
                $context->required('test');
            }
        );
    }

    public function isValidContextPublic($context)
    {
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('isValidContext');
        return $method->invoke($this, $context);
    }
}
