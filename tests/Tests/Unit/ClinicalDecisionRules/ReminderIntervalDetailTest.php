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
        // Create objects using factory methods like the actual code does
        $intervalType = ReminderIntervalType::from('clinical');
        $intervalRange = ReminderIntervalRange::from('pre');
        $amount = 5;
        $timeUnit = TimeUnit::from('day');

        $detail = new ReminderIntervalDetail($intervalType, $intervalRange, $amount, $timeUnit);
        
        $result = $detail->display();
        
        // Expected format: "Warning: 5 Days"
        // The result will contain translated strings, so we just check for the number
        $this->assertStringContainsString('5', $result);
        $this->assertStringContainsString(': ', $result);
    }

    /**
     * Test that display() works with different numeric amounts
     */
    public function testDisplayWithDifferentAmounts(): void
    {
        $intervalType = ReminderIntervalType::from('patient');
        $intervalRange = ReminderIntervalRange::from('post');
        $timeUnit = TimeUnit::from('month');

        // Test with different amounts
        $amounts = [1, 10, 365];
        foreach ($amounts as $amount) {
            $detail = new ReminderIntervalDetail($intervalType, $intervalRange, $amount, $timeUnit);
            $result = $detail->display();
            
            // Verify the numeric amount is present in the output
            $this->assertStringContainsString((string)$amount, $result);
        }
    }
}
