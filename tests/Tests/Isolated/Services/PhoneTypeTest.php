<?php

/**
 * Isolated PhoneType Test
 *
 * Tests PhoneType enum matches() method and enum structure.
 * Note: label() and options() methods use xl() and require database, so are not tested here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\PhoneType;
use PHPUnit\Framework\TestCase;

class PhoneTypeTest extends TestCase
{
    public function testMatchesWithOwnValueReturnsTrue(): void
    {
        // Each case should match its own value
        foreach (PhoneType::cases() as $case) {
            $this->assertTrue(
                $case->matches($case->value),
                sprintf('%s should match its own value %d', $case->name, $case->value)
            );
        }
    }

    public function testMatchesWithStringValueReturnsTrue(): void
    {
        // Each case should match its own value as a string (database returns strings)
        foreach (PhoneType::cases() as $case) {
            $this->assertTrue(
                $case->matches((string)$case->value),
                sprintf('%s should match string "%d"', $case->name, $case->value)
            );
        }
    }

    public function testMatchesReturnsFalseForDifferentCases(): void
    {
        // Each case should NOT match other cases' values
        foreach (PhoneType::cases() as $case) {
            foreach (PhoneType::cases() as $otherCase) {
                if ($case !== $otherCase) {
                    $this->assertFalse(
                        $case->matches($otherCase->value),
                        sprintf('%s should NOT match %s value %d', $case->name, $otherCase->name, $otherCase->value)
                    );
                }
            }
        }
    }

    public function testMatchesReturnsFalseForZero(): void
    {
        foreach (PhoneType::cases() as $case) {
            $this->assertFalse($case->matches(0));
            $this->assertFalse($case->matches('0'));
        }
    }

    public function testMatchesReturnsFalseForNegativeValues(): void
    {
        $this->assertFalse(PhoneType::HOME->matches(-1));
        $this->assertFalse(PhoneType::HOME->matches(-99));
    }

    public function testMatchesReturnsFalseForOutOfRangeValues(): void
    {
        $maxValue = max(array_map(fn($case) => $case->value, PhoneType::cases()));
        $this->assertFalse(PhoneType::HOME->matches($maxValue + 1));
        $this->assertFalse(PhoneType::HOME->matches(99));
        $this->assertFalse(PhoneType::HOME->matches(1000));
    }

    public function testFromThrowsForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        PhoneType::from(99);
    }

    public function testAllEnumValuesArePositiveIntegers(): void
    {
        foreach (PhoneType::cases() as $case) {
            $this->assertGreaterThan(0, $case->value);
        }
    }

    public function testAllEnumValuesAreUnique(): void
    {
        $values = array_map(fn($case) => $case->value, PhoneType::cases());
        $uniqueValues = array_unique($values);

        $this->assertSameSize($values, $uniqueValues);
    }

    public function testEnumNamesAreDescriptive(): void
    {
        $expectedNames = ['HOME', 'WORK', 'CELL', 'EMERGENCY', 'FAX'];
        $actualNames = array_map(fn($case) => $case->name, PhoneType::cases());

        foreach ($expectedNames as $name) {
            $this->assertContains($name, $actualNames);
        }
    }

    public function testRoundTripThroughTryFrom(): void
    {
        // Verify that tryFrom(case->value) returns a case that matches
        foreach (PhoneType::cases() as $case) {
            $restored = PhoneType::tryFrom($case->value);
            // Can't use assertNotNull (tautological), so test the behavior
            $this->assertTrue($case->matches($restored->value));
        }
    }
}
