<?php

/**
 * Isolated tests for RecurrenceStartDateAdjuster.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\RecurrenceStartDateAdjuster;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RecurrenceStartDateAdjusterTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        // Pin the timezone so strtotime()/date() interpret date-only strings
        // identically across environments.
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    #[DataProvider('weekdayAlignmentProvider')]
    public function testAdjustAdvancesToTargetWeekday(
        string $inputDate,
        int $repeatType,
        string $expected
    ): void {
        $this->assertSame($expected, RecurrenceStartDateAdjuster::adjust($inputDate, $repeatType));
    }

    /**
     * Each weekday as a starting date combined with each Mon-Fri repeat type.
     * 2024-01-15 is a Monday; each subsequent row advances by one day.
     *
     * @return array<string, array{string, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function weekdayAlignmentProvider(): array
    {
        return [
            // Already on the target weekday — unchanged
            'Mon -> Mon(5)' => ['2024-01-15', 5, '2024-01-15'],
            'Tue -> Tue(6)' => ['2024-01-16', 6, '2024-01-16'],
            'Wed -> Wed(7)' => ['2024-01-17', 7, '2024-01-17'],
            'Thu -> Thu(8)' => ['2024-01-18', 8, '2024-01-18'],
            'Fri -> Fri(9)' => ['2024-01-19', 9, '2024-01-19'],

            // Advance forward within the same week
            'Mon -> Tue(6)' => ['2024-01-15', 6, '2024-01-16'],
            'Mon -> Wed(7)' => ['2024-01-15', 7, '2024-01-17'],
            'Mon -> Thu(8)' => ['2024-01-15', 8, '2024-01-18'],
            'Mon -> Fri(9)' => ['2024-01-15', 9, '2024-01-19'],
            'Wed -> Fri(9)' => ['2024-01-17', 9, '2024-01-19'],

            // Wrap forward into next week
            'Tue -> Mon(5)' => ['2024-01-16', 5, '2024-01-22'],
            'Fri -> Mon(5)' => ['2024-01-19', 5, '2024-01-22'],
            'Sat -> Mon(5)' => ['2024-01-20', 5, '2024-01-22'],
            'Sun -> Mon(5)' => ['2024-01-21', 5, '2024-01-22'],
            'Sat -> Fri(9)' => ['2024-01-20', 9, '2024-01-26'],
            'Sun -> Fri(9)' => ['2024-01-21', 9, '2024-01-26'],

            // Month boundary crossing
            'EoM Fri -> Mon(5)' => ['2024-01-26', 5, '2024-01-29'],
            'EoM Sun -> Mon(5)' => ['2024-01-28', 5, '2024-01-29'],
            'EoM Wed -> Mon(5)' => ['2024-01-31', 5, '2024-02-05'],

            // Leap-day handling (2024-02-29 is a Thursday)
            'Leap Thu -> Fri(9)' => ['2024-02-29', 9, '2024-03-01'],
            'Leap Thu -> Mon(5)' => ['2024-02-29', 5, '2024-03-04'],
        ];
    }

    #[DataProvider('passThroughRepeatTypeProvider')]
    public function testAdjustPassesThroughNonWeekdayRepeatTypes(int $repeatType): void
    {
        $this->assertSame(
            '2024-01-17',
            RecurrenceStartDateAdjuster::adjust('2024-01-17', $repeatType)
        );
    }

    /**
     * Repeat types outside the 5-9 weekday range must return the date as-is.
     *
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function passThroughRepeatTypeProvider(): array
    {
        return [
            'zero'            => [0],
            'daily (1)'       => [1],
            'weekly (2)'      => [2],
            'monthly (3)'     => [3],
            'yearly (4)'      => [4],
            'above range (10)' => [10],
            'negative'        => [-1],
            'large'           => [99],
        ];
    }

    public function testAdjustReturnsInputWhenDateIsUnparsable(): void
    {
        $this->assertSame(
            'not a date',
            RecurrenceStartDateAdjuster::adjust('not a date', 5)
        );
    }

    public function testAdjustReturnsInputWhenDateIsEmpty(): void
    {
        $this->assertSame(
            '',
            RecurrenceStartDateAdjuster::adjust('', 5)
        );
    }
}
