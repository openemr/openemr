<?php

/**
 * Isolated tests for ConfiguredScheduleHours.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\ConfiguredScheduleHours;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfiguredScheduleHoursTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    /**
     * @return array<string, array{int, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function normalizeWindowProvider(): array
    {
        return [
            'defaults when invalid' => [-1, 0, 8, 17],
            'valid 8-17' => [8, 17, 8, 17],
            'valid 0-24' => [0, 24, 0, 24],
            'end before start bumps end' => [10, 9, 10, 11],
            'end equals start bumps end' => [12, 12, 12, 13],
            'start above 23 defaults start' => [24, 17, 8, 17],
            'end above 24 defaults end' => [8, 25, 8, 17],
            'start 23 end invalid becomes 24' => [23, 0, 23, 24],
        ];
    }

    #[DataProvider('normalizeWindowProvider')]
    public function testNormalizeWindow(
        int $start,
        int $end,
        int $expectedStart,
        int $expectedEnd
    ): void {
        $this->assertSame(
            [$expectedStart, $expectedEnd],
            ConfiguredScheduleHours::normalizeWindow($start, $end)
        );
    }

    /**
     * @return array<string, array{string, int, int, int, int, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function containsSlotProvider(): array
    {
        // 30-minute slots (1800 seconds). Duration of 1 slot = 30 minutes.
        $slotsecs = 1800;

        return [
            'start of day allowed' => ['2024-06-03 08:00:00', 1, $slotsecs, 8, 17, true],
            'last valid 30-min start before end' => ['2024-06-03 16:30:00', 1, $slotsecs, 8, 17, true],
            'start at ending hour excluded' => ['2024-06-03 17:00:00', 1, $slotsecs, 8, 17, false],
            'before start hour excluded' => ['2024-06-03 07:30:00', 1, $slotsecs, 8, 17, false],
            'duration would past end excluded' => ['2024-06-03 16:45:00', 1, $slotsecs, 8, 17, false],
            'two-slot duration ending at boundary' => ['2024-06-03 16:00:00', 2, $slotsecs, 8, 17, true],
            'two-slot duration past boundary' => ['2024-06-03 16:30:00', 2, $slotsecs, 8, 17, false],
            'zero duration slots treated as one' => ['2024-06-03 16:30:00', 0, $slotsecs, 8, 17, true],
            'invalid window falls back to defaults' => ['2024-06-03 08:00:00', 1, $slotsecs, -5, -5, true],
            'midnight start window' => ['2024-06-03 00:00:00', 1, $slotsecs, 0, 1, true],
        ];
    }

    #[DataProvider('containsSlotProvider')]
    public function testContainsSlot(
        string $dateTime,
        int $durationSlots,
        int $slotsecs,
        int $startHour,
        int $endHour,
        bool $expected
    ): void {
        $utime = strtotime($dateTime);
        $this->assertNotFalse($utime);
        $this->assertSame(
            $expected,
            ConfiguredScheduleHours::containsSlot(
                $utime,
                $durationSlots,
                $slotsecs,
                $startHour,
                $endHour
            )
        );
    }
}
