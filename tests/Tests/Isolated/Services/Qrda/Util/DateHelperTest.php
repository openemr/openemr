<?php

/**
 * Isolated DateHelper Test
 *
 * Tests QRDA DateHelper formatting utilities.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Qrda\Util;

use OpenEMR\Services\Qrda\Util\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    public function testFormatDatetimeCqmWithValidDatetime(): void
    {
        $result = DateHelper::format_datetime_cqm('2024-06-15 14:30:00');
        $this->assertSame('2024-06-15T14:30:00.000+00:00', $result);
    }

    public function testFormatDatetimeCqmWithDateOnly(): void
    {
        $result = DateHelper::format_datetime_cqm('2024-06-15');
        $this->assertSame('2024-06-15T00:00:00.000+00:00', $result);
    }

    public function testFormatDatetimeCqmWithMidnight(): void
    {
        $result = DateHelper::format_datetime_cqm('2024-01-01 00:00:00');
        $this->assertSame('2024-01-01T00:00:00.000+00:00', $result);
    }

    public function testFormatDatetimeCqmWithEndOfDay(): void
    {
        $result = DateHelper::format_datetime_cqm('2024-12-31 23:59:59');
        $this->assertSame('2024-12-31T23:59:59.000+00:00', $result);
    }

    public function testFormatDatetimeWithValidDatetime(): void
    {
        $result = DateHelper::format_datetime('2024-06-15 14:30:45');
        $this->assertSame('20240615143045', $result);
    }

    public function testFormatDatetimeWithDateOnly(): void
    {
        $result = DateHelper::format_datetime('2024-06-15');
        $this->assertSame('20240615000000', $result);
    }

    public function testFormatDateWithValidDate(): void
    {
        $result = DateHelper::format_date('2024-06-15');
        $this->assertSame('20240615', $result);
    }

    public function testFormatDateWithDatetime(): void
    {
        // Time component should be ignored
        $result = DateHelper::format_date('2024-06-15 14:30:00');
        $this->assertSame('20240615', $result);
    }

    public function testFormatDateWithDifferentInputFormats(): void
    {
        // strtotime handles various formats
        $this->assertSame('20240615', DateHelper::format_date('June 15, 2024'));
        $this->assertSame('20240615', DateHelper::format_date('15-Jun-2024'));
    }

    public function testFormatDatetimeCqmOutputFormat(): void
    {
        // Verify ISO 8601 format with milliseconds and timezone
        $result = DateHelper::format_datetime_cqm('2024-03-10 08:15:30');
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.000\+00:00$/',
            $result
        );
    }

    public function testFormatDatetimeOutputFormat(): void
    {
        // Verify QRDA XML format (YYYYMMDDHHmmss)
        $result = DateHelper::format_datetime('2024-03-10 08:15:30');
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{14}$/', $result);
    }

    public function testFormatDateOutputFormat(): void
    {
        // Verify YYYYMMDD format
        $result = DateHelper::format_date('2024-03-10');
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{8}$/', $result);
    }

    public function testLeapYearDate(): void
    {
        $result = DateHelper::format_date('2024-02-29');
        $this->assertSame('20240229', $result);
    }

    public function testYearBoundaries(): void
    {
        $this->assertSame('20231231', DateHelper::format_date('2023-12-31'));
        $this->assertSame('20240101', DateHelper::format_date('2024-01-01'));
    }

    public function testFormatDatetimeCqmPreservesTime(): void
    {
        // Test that seconds are preserved
        $result = DateHelper::format_datetime_cqm('2024-06-15 12:34:56');
        $this->assertIsString($result);
        $this->assertStringContainsString('12:34:56', $result);
    }

    public function testFormatDatetimeCqmWithVariousTimezoneFormats(): void
    {
        // Different input formats should produce consistent output
        $result1 = DateHelper::format_datetime_cqm('2024-06-15 14:30:00');
        $result2 = DateHelper::format_datetime_cqm('2024-06-15T14:30:00');
        $this->assertSame($result1, $result2);
    }

    public function testFormatDatetimeWithSingleDigitValues(): void
    {
        // Ensure zero-padding works correctly
        $result = DateHelper::format_datetime('2024-01-05 03:07:09');
        $this->assertSame('20240105030709', $result);
    }

    public function testFormatDateMonthBoundaries(): void
    {
        // Test first and last days of various months
        $this->assertSame('20240131', DateHelper::format_date('2024-01-31'));
        $this->assertSame('20240430', DateHelper::format_date('2024-04-30'));
        $this->assertSame('20241031', DateHelper::format_date('2024-10-31'));
    }
}
