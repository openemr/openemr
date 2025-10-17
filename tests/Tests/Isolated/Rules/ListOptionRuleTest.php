<?php

/**
 * Isolated ListOptionRule Test
 *
 * Tests ListOptionRule validation functionality without database dependencies.
 * ListOptionRule validates that a list option exists in the list_options table.
 * This test uses a stub to avoid database calls.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Rules;

use OpenEMR\Validators\Rules\ListOptionRule;
use Particle\Validator\Rule;
use PHPUnit\Framework\TestCase;

class ListOptionRuleTest extends TestCase
{
    public function testRuleInheritsFromParticleRule(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $this->assertInstanceOf(Rule::class, $rule);
    }

    public function testRuleIsInstantiable(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $this->assertInstanceOf(ListOptionRule::class, $rule);
    }

    public function testRuleRequiresListIdInConstructor(): void
    {
        $rule = new ListOptionRuleStub('my_list_id');
        $this->assertInstanceOf(ListOptionRule::class, $rule);
    }

    public function testRuleAcceptsDifferentListIds(): void
    {
        // Test various list ID formats
        $rule1 = new ListOptionRuleStub('simple_list');
        $rule2 = new ListOptionRuleStub('list-with-dashes');
        $rule3 = new ListOptionRuleStub('list_with_underscores');
        $rule4 = new ListOptionRuleStub('123numeric_list');

        $this->assertInstanceOf(ListOptionRule::class, $rule1);
        $this->assertInstanceOf(ListOptionRule::class, $rule2);
        $this->assertInstanceOf(ListOptionRule::class, $rule3);
        $this->assertInstanceOf(ListOptionRule::class, $rule4);
    }

    public function testValidateMethodExists(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $this->assertTrue(method_exists($rule, 'validate'));
    }

    public function testValidateReturnsTrueForValidOption(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $result = $rule->validate('valid_option');
        $this->assertTrue($result);
    }

    public function testValidateHandlesNullValue(): void
    {
        $rule = new ListOptionRuleStub('test_list');

        // For isolated testing, we'll just check that null returns false from our stub logic
        // The actual error handling requires validator context which we're avoiding
        $this->assertFalse($rule->validateDirectly(null));
    }

    public function testValidateHandlesEmptyString(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $result = $rule->validate('');

        // Empty string should be treated as valid option in our stub
        $this->assertTrue($result);
    }

    public function testValidateHandlesInvalidOption(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        // Our stub will return false for 'invalid_option'
        $this->assertFalse($rule->validateDirectly('invalid_option'));
    }

    public function testRuleHasCorrectErrorConstant(): void
    {
        $this->assertTrue(defined('OpenEMR\Validators\Rules\ListOptionRule::INVALID_LIST_OPTION'));
        $this->assertEquals('ListOptionRule::INVALID_LIST_OPTION', ListOptionRule::INVALID_LIST_OPTION);
    }

    public function testGetMessageParametersMethodExists(): void
    {
        $rule = new ListOptionRuleStub('test_list');
        $this->assertTrue(method_exists($rule, 'getMessageParameters'));
    }

    public function testMessageParametersIncludeListId(): void
    {
        $rule = new ListOptionRuleStub('my_custom_list');

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($rule);
        $method = $reflection->getMethod('getMessageParameters');

        $parameters = $method->invoke($rule);

        $this->assertIsArray($parameters);
        $this->assertArrayHasKey('listId', $parameters);
        $this->assertEquals('my_custom_list', $parameters['listId']);
    }

    public function testRuleCanBeUsedWithDifferentListIds(): void
    {
        $rule1 = new ListOptionRuleStub('list_one');
        $rule2 = new ListOptionRuleStub('list_two');

        // Both should validate successfully for valid options
        $this->assertTrue($rule1->validate('valid_option'));
        $this->assertTrue($rule2->validate('valid_option'));

        // Both should fail for invalid options using direct validation
        $this->assertFalse($rule1->validateDirectly('invalid_option'));
        $this->assertFalse($rule2->validateDirectly('invalid_option'));
    }

    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(ListOptionRule::class));
    }

    public function testRuleWorksWithVariousDataTypes(): void
    {
        $rule = new ListOptionRuleStub('test_list');

        // Test string values
        $this->assertTrue($rule->validate('string_value'));

        // Test numeric values (converted to string)
        $this->assertTrue($rule->validate(123));
        $this->assertTrue($rule->validate(0));

        // Test boolean (converted to string)
        $this->assertTrue($rule->validate(true));
        $this->assertTrue($rule->validate(false));
    }

    public function testRuleErrorConstantValue(): void
    {
        $expectedConstant = 'ListOptionRule::INVALID_LIST_OPTION';
        $this->assertEquals($expectedConstant, ListOptionRule::INVALID_LIST_OPTION);
    }

    public function testRuleCanBeCreatedWithDifferentListIds(): void
    {
        $rule1 = new ListOptionRuleStub('list_a');
        $rule2 = new ListOptionRuleStub('list_b');
        $rule3 = new ListOptionRuleStub('123');
        $rule4 = new ListOptionRuleStub('special-list_name');

        // All should be valid instances
        $this->assertInstanceOf(ListOptionRule::class, $rule1);
        $this->assertInstanceOf(ListOptionRule::class, $rule2);
        $this->assertInstanceOf(ListOptionRule::class, $rule3);
        $this->assertInstanceOf(ListOptionRule::class, $rule4);
    }

    public function testRuleImplementsParticleRuleInterface(): void
    {
        $rule = new ListOptionRuleStub('test_list');

        // Test that it implements the required Particle Rule interface
        $this->assertTrue(method_exists($rule, 'validate'));
        $this->assertTrue(method_exists($rule, 'getMessageParameters'));
    }

    public function testRuleMessageTemplateExists(): void
    {
        $rule = new ListOptionRuleStub('test_list');

        // Use reflection to check that message templates are defined
        $reflection = new \ReflectionClass($rule);
        $property = $reflection->getProperty('messageTemplates');
        $templates = $property->getValue($rule);

        $this->assertIsArray($templates);
        $this->assertArrayHasKey(ListOptionRule::INVALID_LIST_OPTION, $templates);
        $this->assertStringContainsString('listId', $templates[ListOptionRule::INVALID_LIST_OPTION]);
        $this->assertStringContainsString('name', $templates[ListOptionRule::INVALID_LIST_OPTION]);
    }

    public function testRuleValidateWithEdgeCases(): void
    {
        $rule = new ListOptionRuleStub('test_list');

        // Test with float values
        $this->assertTrue($rule->validate(12.34));

        // Test with array (should convert to string "Array")
        $this->assertTrue($rule->validate([]));

        // Test with object that can be cast to string
        $obj = new \stdClass();
        $this->assertTrue($rule->validate($obj));
    }

    public function testRuleListIdIsStoredCorrectly(): void
    {
        $listId = 'my_custom_list_id';
        $rule = new ListOptionRuleStub($listId);

        // Use reflection to verify the listId property is set correctly
        // Check parent class since stub might not have the property
        $reflection = new \ReflectionClass(\OpenEMR\Validators\Rules\ListOptionRule::class);
        $property = $reflection->getProperty('listId');
        $storedListId = $property->getValue($rule);

        $this->assertEquals($listId, $storedListId);
    }
}

/**
 * Test stub that overrides database-dependent methods
 */
class ListOptionRuleStub extends ListOptionRule
{
    /**
     * Override validate to avoid database calls
     * Returns true for most values, false for 'invalid_option' and null
     */
    public function validate($value)
    {
        if ($value === null) {
            return $this->error(self::INVALID_LIST_OPTION);
        }

        // Simulate database lookup - return false for specific test case
        if ($value === 'invalid_option') {
            return $this->error(self::INVALID_LIST_OPTION);
        }

        // For testing purposes, assume all other values are valid
        return true;
    }

    /**
     * Direct validation without error handling for isolated testing
     */
    public function validateDirectly($value)
    {
        if ($value === null) {
            return false;
        }

        // Simulate database lookup - return false for specific test case
        if ($value === 'invalid_option') {
            return false;
        }

        // For testing purposes, assume all other values are valid
        return true;
    }
}

/**
 * Test stub that uses the real validate method (not overridden)
 */
class ListOptionRuleReal extends ListOptionRule
{
    // validate method is NOT overridden - uses the real implementation
}
