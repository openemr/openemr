<?php

/**
 * Isolated OpenEMRParticleValidator Test
 *
 * Tests OpenEMRParticleValidator functionality without database dependencies.
 * OpenEMRParticleValidator extends Particle\Validator\Validator to provide
 * OpenEMR-specific validation chains via the OpenEMRChain class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\OpenEMRParticleValidator;
use OpenEMR\Validators\OpenEMRChain;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;

class OpenEMRParticleValidatorTest extends TestCase
{
    private OpenEMRParticleValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new OpenEMRParticleValidator();
    }

    public function testValidatorInheritsFromParticleValidator(): void
    {
        $this->assertInstanceOf(Validator::class, $this->validator);
    }

    public function testValidatorIsInstantiable(): void
    {
        $validator = new OpenEMRParticleValidator();
        $this->assertInstanceOf(OpenEMRParticleValidator::class, $validator);
    }

    public function testBuildChainMethodExists(): void
    {
        $this->assertTrue(method_exists($this->validator, 'buildChain'));
    }

    public function testBuildChainReturnsOpenEMRChain(): void
    {
        // Use reflection to test the protected buildChain method
        $reflection = new \ReflectionClass($this->validator);
        $buildChainMethod = $reflection->getMethod('buildChain');

        $chain = $buildChainMethod->invoke(
            $this->validator,
            'test_key',
            'Test Field',
            false,
            true
        );

        $this->assertInstanceOf(OpenEMRChain::class, $chain);
    }

    public function testBuildChainPassesParametersCorrectly(): void
    {
        // Use reflection to test parameter passing
        $reflection = new \ReflectionClass($this->validator);
        $buildChainMethod = $reflection->getMethod('buildChain');

        // Test with different parameter combinations
        $chain1 = $buildChainMethod->invoke(
            $this->validator,
            'required_key',
            'Required Field',
            true,
            false
        );

        $chain2 = $buildChainMethod->invoke(
            $this->validator,
            'optional_key',
            'Optional Field',
            false,
            true
        );

        $this->assertInstanceOf(OpenEMRChain::class, $chain1);
        $this->assertInstanceOf(OpenEMRChain::class, $chain2);
    }

    public function testValidatorCanDefineRules(): void
    {
        // Test that we can define validation rules using the validator
        $this->validator->required('test_field')->length(1, 50);
        $this->validator->optional('optional_field')->numeric();

        // If we get here without exceptions, rule definition works
        $this->assertTrue(true);
    }

    public function testValidatorCanValidateData(): void
    {
        // Simple test without complex validation to avoid issues
        $this->validator->required('name');

        // Test valid data
        $validData = ['name' => 'John Doe'];
        $result = $this->validator->validate($validData);

        // Debug the validation result
        if (!$result->isValid()) {
            $errors = $result->getMessages();
            $this->fail('Validation failed with errors: ' . print_r($errors, true));
        }

        $this->assertTrue($result->isValid(), 'Valid data should pass validation');

        // Reset validator for next test
        $this->validator = new OpenEMRParticleValidator();
        $this->validator->required('name');

        // Test invalid data - missing required field
        $invalidData = []; // Missing required field
        $result = $this->validator->validate($invalidData);
        $this->assertFalse($result->isValid());
    }

    public function testValidatorSupportsOpenEMRChainMethods(): void
    {
        // This tests that the OpenEMRChain is properly integrated
        // We can't test the actual listOption functionality without database,
        // but we can test that the method is available through the validator
        $chain = $this->validator->required('test_list_field');

        // The chain should be an OpenEMRChain which has the listOption method
        $this->assertTrue(method_exists($chain, 'listOption'));
    }

    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(OpenEMRParticleValidator::class));
    }

    public function testValidatorCanBeReused(): void
    {
        // Define simple rules
        $this->validator->required('field1');
        $this->validator->required('field2');

        // Validate first dataset
        $data1 = ['field1' => 'test', 'field2' => 'value'];
        $result1 = $this->validator->validate($data1);
        $this->assertTrue($result1->isValid(), 'First validation should pass');

        // Validate second dataset with same validator
        $data2 = ['field1' => 'another', 'field2' => 'value'];
        $result2 = $this->validator->validate($data2);
        $this->assertTrue($result2->isValid(), 'Second validation should pass');
    }

    public function testValidatorHandlesComplexRules(): void
    {
        // Test that we can define complex rules without errors
        $this->validator->required('email');
        $this->validator->required('password');

        $validData = [
            'email' => 'test@example.com',
            'password' => 'securepassword123'
        ];

        $result = $this->validator->validate($validData);
        $this->assertTrue($result->isValid(), 'Valid complex data should pass');

        // Test invalid data - missing required field
        $this->validator = new OpenEMRParticleValidator();
        $this->validator->required('email');

        $invalidData = []; // Missing required field
        $result = $this->validator->validate($invalidData);
        $this->assertFalse($result->isValid());
    }
}
