<?php

/**
 * ListOptionRuleTest - Tests the ListOptionRule class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Validators\Rules;

use OpenEMR\Validators\Rules\ListOptionRule;
use Particle\Validator\MessageStack;
use PHPUnit\Framework\TestCase;

class ListOptionRuleTest extends TestCase
{
    public function testValidateWithValidOptionId(): void
    {
        // test with a valid list id
        $rule = new ListOptionRule('yesno');
        $messageStack = new MessageStack();
        $rule->setMessageStack($messageStack);
        $this->assertTrue($rule->validate('NO'), 'NO is a valid option id for the yesno list');
        $this->assertEmpty($messageStack->getFailures(), "No failures should have been added to the message stack");
    }

    public function testValidateWithInvalidOptionId(): void
    {
        $rule = new ListOptionRule('yesno');
        $messageStack = new MessageStack();
        $rule->setMessageStack($messageStack);
        $rule->setParameters('field_key', 'Human Name'); // normally this would be done by a framework
        $this->assertFalse($rule->validate('somethingelse'), 'somethingelse is not a valid option id for the yesno list');
        $this->assertNotEmpty($messageStack->getFailures(), "Failures should have been added to the message stack");
    }
}
