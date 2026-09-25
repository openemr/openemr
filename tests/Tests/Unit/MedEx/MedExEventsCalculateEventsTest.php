<?php

/**
 * MedExEventsCalculateEventsTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\MedEx;

use MedExApi\Events;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MedExEventsCalculateEventsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Loaded after globals.php, the way library/MedEx/MedEx_background.php loads it.
        require_once __DIR__ . '/../../../../library/MedEx/API.php';
    }

    /**
     * "Nth weekday of the month" appointments (pc_recurrtype 2) and the dates they fall on.
     *
     * @return array<string, array{string, string, string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nthWeekdayProvider(): array
    {
        return [
            'second Tuesday, one month' => ['2', '2', '2026-10-01', '2026-10-31', ['2026-10-13']],
            'second Tuesday, two months' => ['2', '2', '2026-10-01', '2026-11-30', ['2026-10-13', '2026-11-10']],
            // November 2026 has four Fridays, so "fifth" falls back to the last one.
            'fifth Friday falls back to the last' => ['5', '5', '2026-10-01', '2026-11-30', ['2026-10-30', '2026-11-27']],
            // Out-of-range specs yield no dates instead of looping down from the stored week.
            'week 0 is out of range' => ['0', '2', '2026-10-01', '2026-10-31', []],
            'week 1000000 is out of range' => ['1000000', '2', '2026-10-01', '2026-10-31', []],
            'day 7 is out of range' => ['2', '7', '2026-10-01', '2026-10-31', []],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('nthWeekdayProvider')]
    public function nthWeekdayRecurrenceListsItsDates(
        string $weekOfMonth,
        string $dayOfWeek,
        string $start,
        string $stop,
        array $expected
    ): void {
        $appointment = [
            'pc_recurrtype' => '2',
            'pc_recurrspec' => serialize([
                'event_repeat_freq' => '',
                'event_repeat_freq_type' => '',
                'event_repeat_on_num' => $weekOfMonth,
                'event_repeat_on_day' => $dayOfWeek,
                'event_repeat_on_freq' => '1',
                'exdate' => '',
            ]),
            'pc_eventDate' => '2026-01-13',
            'pc_endDate' => '2026-12-31',
        ];

        $this->assertSame($expected, $this->calculateEvents($appointment, $start, $stop));
    }

    /**
     * Weekly appointments (pc_recurrtype 1), which step through Events::__increment().
     *
     * @return array<string, array{string, string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function everyWeekProvider(): array
    {
        return [
            'starts inside the range' => ['2026-10-06', '2026-10-01', '2026-10-31', ['2026-10-06', '2026-10-13', '2026-10-20', '2026-10-27']],
            // steps forward from the first appointment to the start of the range first
            'starts before the range' => ['2026-09-01', '2026-10-01', '2026-10-31', ['2026-10-06', '2026-10-13', '2026-10-20', '2026-10-27']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('everyWeekProvider')]
    public function weeklyRecurrenceListsItsDates(string $eventDate, string $start, string $stop, array $expected): void
    {
        $appointment = [
            'pc_recurrtype' => '1',
            'pc_recurrspec' => serialize([
                'event_repeat_freq' => '1',
                'event_repeat_freq_type' => '1',
                'event_repeat_on_num' => '',
                'event_repeat_on_day' => '',
                'event_repeat_on_freq' => '',
                'exdate' => '',
            ]),
            'pc_eventDate' => $eventDate,
            'pc_endDate' => '2026-12-31',
        ];

        $this->assertSame($expected, $this->calculateEvents($appointment, $start, $stop));
    }

    /**
     * Runs calculateEvents() and fails on any PHP warning or notice it raises;
     * phpunit.xml reports those without failing the test.
     *
     * @param array<string, string> $appointment
     * @return mixed
     */
    private function calculateEvents(array $appointment, string $start, string $stop): mixed
    {
        // calculateEvents() reaches __increment() through the MedEx object, as in production
        $medex = new \stdClass();
        $medex->curl = null;
        $events = new Events($medex);
        $medex->events = $events;

        $raised = [];
        set_error_handler(function (int $errno, string $message) use (&$raised): bool {
            $raised[] = $message;
            return true;
        });
        try {
            $result = $events->calculateEvents($appointment, $start, $stop);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $raised);
        return $result;
    }
}
