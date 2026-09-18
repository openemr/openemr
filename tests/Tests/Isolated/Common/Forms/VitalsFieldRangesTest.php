<?php

/**
 * Isolated VitalsFieldRanges Test
 *
 * Tests validation range definitions for vitals form fields.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Davit Mnatsakanyan
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms;

use OpenEMR\Common\Forms\VitalsFieldRanges;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VitalsFieldRangesTest extends TestCase
{
    private const EXPECTED_FIELDS = [
        'weight',
        'height',
        'bps',
        'bpd',
        'pulse',
        'respiration',
        'temperature',
        'oxygen_saturation',
        'oxygen_flow_rate',
        'inhaled_oxygen_concentration',
        'head_circ',
        'waist_circ',
        'ped_weight_height',
        'ped_bmi',
        'ped_head_circ',
    ];

    private const METRIC_FIELDS = [
        'weight',
        'height',
        'temperature',
        'head_circ',
        'waist_circ',
    ];

    public function testEveryExpectedFieldHasARangeEntry(): void
    {
        $ranges = VitalsFieldRanges::getRanges();
        foreach (self::EXPECTED_FIELDS as $field) {
            $this->assertArrayHasKey($field, $ranges, "Missing range entry for field '{$field}'");
        }
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fieldProvider(): array
    {
        $cases = [];
        foreach (self::EXPECTED_FIELDS as $field) {
            $cases[$field] = [$field];
        }
        return $cases;
    }

    #[DataProvider('fieldProvider')]
    public function testRangesAreSelfConsistent(string $field): void
    {
        $range = VitalsFieldRanges::getRangeForField($field);
        $this->assertNotNull($range);

        // assertLessThanOrEqual($expected, $actual) asserts $actual <= $expected
        // Verify min <= warningMin <= warningMax <= max
        $this->assertLessThanOrEqual($range['warningMin'], $range['min'], "min <= warningMin for {$field}");
        $this->assertLessThanOrEqual($range['warningMax'], $range['warningMin'], "warningMin <= warningMax for {$field}");
        $this->assertLessThanOrEqual($range['max'], $range['warningMax'], "warningMax <= max for {$field}");
    }

    #[DataProvider('fieldProvider')]
    public function testWarningBandsAreNotInert(string $field): void
    {
        $range = VitalsFieldRanges::getRangeForField($field);
        $this->assertNotNull($range);

        $hasDistinctMin = $range['warningMin'] > $range['min'];
        $hasDistinctMax = $range['warningMax'] < $range['max'];

        $this->assertTrue(
            $hasDistinctMin || $hasDistinctMax,
            "Warning band for '{$field}' is identical to hard bounds — warnings can never trigger"
        );
    }

    public function testGetRangeForFieldReturnsNullForUnknownField(): void
    {
        $this->assertNull(VitalsFieldRanges::getRangeForField('nonexistent_field'));
        $this->assertNull(VitalsFieldRanges::getRangeForField(''));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function metricFieldProvider(): array
    {
        $cases = [];
        foreach (self::METRIC_FIELDS as $field) {
            $cases[$field] = [$field];
        }
        return $cases;
    }

    #[DataProvider('metricFieldProvider')]
    public function testMetricFieldsHaveMetricRanges(string $field): void
    {
        $range = VitalsFieldRanges::getRangeForField($field);
        $this->assertNotNull($range);
        $this->assertArrayHasKey('metricMin', $range, "Missing metricMin for '{$field}'");
        $this->assertArrayHasKey('metricMax', $range, "Missing metricMax for '{$field}'");
        $this->assertArrayHasKey('metricWarningMin', $range, "Missing metricWarningMin for '{$field}'");
        $this->assertArrayHasKey('metricWarningMax', $range, "Missing metricWarningMax for '{$field}'");

        $this->assertLessThanOrEqual($range['metricWarningMin'], $range['metricMin'], "metricMin <= metricWarningMin for {$field}");
        $this->assertLessThanOrEqual($range['metricWarningMax'], $range['metricWarningMin'], "metricWarningMin <= metricWarningMax for {$field}");
        $this->assertLessThanOrEqual($range['metricMax'], $range['metricWarningMax'], "metricWarningMax <= metricMax for {$field}");
    }
}
