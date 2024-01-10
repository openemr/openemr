<?php

namespace OpenEMR\Tests\Validators\Rules;

use OpenEMR\Validators\Rules\ListOptionRule;
use Particle\Validator\MessageStack;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenEMR\Validators\Rules\ListOptionRule
 */
class ListOptionRuleTest extends TestCase
{
    /**
     * @covers ::validate
     */
    public function testValidateWithValidOptionId()
    {
        // test with a valid list id
        $rule = new ListOptionRule('yesno');
        $messageStack = new MessageStack();
        $rule->setMessageStack($messageStack);
        $this->assertTrue($rule->validate('NO'), 'NO is a valid option id for the yesno list');
        $this->assertEmpty($messageStack->getFailures(), "No failures should have been added to the message stack");
    }

    public function testValidateWithInvalidOptionId()
    {
        $rule = new ListOptionRule('yesno');
        $messageStack = new MessageStack();
        $rule->setMessageStack($messageStack);
        $this->assertFalse($rule->validate('somethingelse'), 'somethingelse is not a valid option id for the yesno list');
        $this->assertNotEmpty($messageStack->getFailures(), "Failures should have been added to the message stack");
    }
}
