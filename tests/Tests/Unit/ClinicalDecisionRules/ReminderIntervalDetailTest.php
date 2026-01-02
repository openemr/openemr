<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalDetail;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalRange;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\ReminderIntervalType;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\TimeUnit;
use PHPUnit\Framework\TestCase;

class ReminderIntervalDetailTest extends TestCase
{
    /**
     * Test that display() method properly formats the interval detail
     * and handles numeric amount correctly (without unnecessary translation)
     */
    public function testDisplayFormatsCorrectly(): void
    {
        // Create mock objects with lbl properties
        $intervalType = new ReminderIntervalType('clinical', 'Clinical');
        $intervalRange = new ReminderIntervalType('pre', 'Warning');
        $amount = 5;
        $timeUnit = new TimeUnit('day', 'Days');

        $detail = new ReminderIntervalDetail($intervalType, $intervalRange, $amount, $timeUnit);
        
        $result = $detail->display();
        
        // Expected format: "Warning: 5 Days"
        $this->assertStringContainsString('Warning', $result);
        $this->assertStringContainsString('5', $result);
        $this->assertStringContainsString('Days', $result);
        $this->assertStringContainsString(': ', $result);
        $this->assertEquals('Warning: 5 Days', $result);
    }

    /**
     * Test that display() works with different numeric amounts
     */
    public function testDisplayWithDifferentAmounts(): void
    {
        $intervalType = new ReminderIntervalType('patient', 'Patient');
        $intervalRange = new ReminderIntervalType('post', 'Past due');
        $timeUnit = new TimeUnit('month', 'Months');

        // Test with different amounts
        $amounts = [1, 10, 365];
        foreach ($amounts as $amount) {
            $detail = new ReminderIntervalDetail($intervalType, $intervalRange, $amount, $timeUnit);
            $result = $detail->display();
            
            $this->assertStringContainsString((string)$amount, $result);
            $this->assertStringContainsString('Past due', $result);
            $this->assertStringContainsString('Months', $result);
        }
    }
}
