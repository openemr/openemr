<?php

/**
 * Isolated PatientFinderAppointments Test
 *
 * Next and Last Appt without a database: one-off appointments, repeating series expanded
 * over the window around now (or around a series' end), and the site's display formats.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Services\PatientFinder\PatientFinderAppointments;
use OpenEMR\Services\PatientFinder\PatientFinderBuiltInColumn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientFinderAppointmentsTest extends TestCase
{
    private const NOW = '2026-11-13 12:00:00';

    /** @var list<array{string, string, int}> Each series expansion asked for: from, to, pc_eid. */
    private array $expansions = [];

    /**
     * Lookups over the given appointment rows; a series expands to the given dates.
     *
     * @param list<array<string, string|int>>    $rows   what the appointment query returns
     * @param array<int, list<string>>           $series pc_eid => its "Y-m-d H:i:s" dates in the window
     */
    private function appointments(array $rows, array $series = [], int $dateFormat = 0, int $timeFormat = 0): PatientFinderAppointments
    {
        return new PatientFinderAppointments(
            static fn(string $sql, array $binds): array => $rows,
            function (string $from, string $to, int $eid) use ($series): array {
                $this->expansions[] = [$from, $to, $eid];
                $dates = [];
                foreach ($series[$eid] ?? [] as $at) {
                    [$date, $time] = explode(' ', $at);
                    if ($date >= $from && $date <= $to) {
                        $dates[] = ['pc_eventDate' => $date, 'pc_startTime' => $time];
                    }
                }
                return $dates;
            },
            new FrozenClock(new DateTimeImmutable(self::NOW)),
            $dateFormat,
            $timeFormat
        );
    }

    /**
     * A pc_recurrspec for "every N units" (type 1), with any excluded dates.
     */
    private static function everySpec(int $freq, int $unit, string $exdate = ''): string
    {
        return serialize([
            'event_repeat_freq' => (string) $freq,
            'event_repeat_freq_type' => (string) $unit,
            'event_repeat_on_num' => '1',
            'event_repeat_on_day' => '0',
            'event_repeat_on_freq' => '0',
            'exdate' => $exdate,
        ]);
    }

    /**
     * A series' dates, every given number of days from a start, leaving out excluded dates.
     *
     * @param list<string> $excluded Y-m-d dates
     * @return list<string>
     */
    private static function everyDays(string $start, int $days, int $count, string $time, array $excluded = []): array
    {
        $dates = [];
        $at = new DateTimeImmutable($start);
        for ($i = 0; $i < $count; $i++) {
            if (!in_array($at->format('Y-m-d'), $excluded, true)) {
                $dates[] = $at->format('Y-m-d') . ' ' . $time;
            }
            $at = $at->modify("+$days days");
        }
        return $dates;
    }

    /**
     * @param list<array<string, string|int>> $rows
     * @param array<int, list<string>>        $series
     * @return array<string, string>
     */
    private function valuesFor(array $rows, array $series = []): array
    {
        return $this->appointments($rows, $series)->values(
            [7],
            [PatientFinderBuiltInColumn::NextAppointment, PatientFinderBuiltInColumn::LastAppointment]
        )[7] ?? [];
    }

    /**
     * One-off appointments: the soonest after now and the latest before now.
     */
    public function testOneOffAppointmentsPickTheNearestEitherSideOfNow(): void
    {
        $values = $this->valuesFor([
            ['pc_eid' => 1, 'pc_eventDate' => '2026-10-01', 'pc_startTime' => '10:00:00', 'pc_recurrtype' => 0],
            ['pc_eid' => 2, 'pc_eventDate' => '2026-11-05', 'pc_startTime' => '14:30:00', 'pc_recurrtype' => 0],
            ['pc_eid' => 3, 'pc_eventDate' => '2026-12-20', 'pc_startTime' => '08:00:00', 'pc_recurrtype' => 0],
            ['pc_eid' => 4, 'pc_eventDate' => '2026-11-14', 'pc_startTime' => '13:45:00', 'pc_recurrtype' => 0],
        ]);

        $this->assertSame(['next_appointment' => '2026-11-14 13:45', 'last_appointment' => '2026-11-05 14:30'], $values);
        $this->assertSame([], $this->expansions, 'a one-off appointment is not expanded');
    }

    /**
     * A weekly series that started months ago: its next date is next week, and its last
     * date is last week, not the series' start. Only a week or so either side is expanded.
     */
    public function testARunningSeriesCountsItsNearestDates(): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 9, 'pc_eventDate' => '2026-08-03', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '09:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 1),
            ]],
            [9 => self::everyDays('2026-08-03', 7, 60, '09:00:00')]
        );

        $this->assertSame(['next_appointment' => '2026-11-16 09:00', 'last_appointment' => '2026-11-09 09:00'], $values);
        $this->assertSame([['2026-11-13', '2026-11-21', 9], ['2026-11-05', '2026-11-13', 9]], $this->expansions);
    }

    /**
     * A series repeating every three years: its next date is years away, beyond any fixed
     * window, and its last date is its start.
     */
    public function testALongPeriodSeriesFindsItsNextDate(): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 4, 'pc_eventDate' => '2026-06-01', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '10:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(3, 3),
            ]],
            [4 => ['2026-06-01 10:00:00', '2029-06-01 10:00:00', '2032-06-01 10:00:00']]
        );

        $this->assertSame(['next_appointment' => '2029-06-01 10:00', 'last_appointment' => '2026-06-01 10:00'], $values);
    }

    /**
     * A run of excluded dates is searched past: the window doubles until a date turns up.
     */
    public function testARunOfExcludedDatesIsSearchedPast(): void
    {
        $excluded = ['2026-11-16', '2026-11-23', '2026-11-30', '2026-12-07', '2026-12-14'];
        $values = $this->valuesFor(
            [[
                'pc_eid' => 3, 'pc_eventDate' => '2026-08-03', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '09:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 1, implode(',', $excluded)),
            ]],
            [3 => self::everyDays('2026-08-03', 7, 60, '09:00:00', $excluded)]
        );

        $this->assertSame('2026-12-21 09:00', $values['next_appointment'] ?? null);
    }

    /**
     * A series that ended years ago is searched back from its end, where its last date is.
     */
    public function testASeriesThatEndedLongAgoStillGivesItsLastDate(): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 5, 'pc_eventDate' => '2020-01-06', 'pc_endDate' => '2023-06-30', 'pc_startTime' => '11:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 1),
            ]],
            [5 => self::everyDays('2020-01-06', 7, 183, '11:00:00')]
        );

        $this->assertSame(['last_appointment' => '2023-06-26 11:00'], $values);
        $this->assertSame([['2023-06-22', '2023-06-30', 5]], $this->expansions, 'nothing is searched after its end');
    }

    /**
     * A series that starts in the future is expanded too, so an excluded first date is not
     * shown as its next.
     */
    public function testAFutureSeriesSkipsItsExcludedFirstDate(): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 6, 'pc_eventDate' => '2028-02-01', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '15:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 2, '2028-02-01'),
            ]],
            [6 => ['2028-03-01 15:00:00', '2028-04-01 15:00:00']]
        );

        $this->assertSame(['next_appointment' => '2028-03-01 15:00'], $values);
    }

    /**
     * An "Nth weekday every N months" series (type 2) uses its month interval.
     */
    public function testAMonthlyWeekdaySeriesUsesItsMonthInterval(): void
    {
        $spec = serialize(['event_repeat_on_freq' => '2', 'event_repeat_on_num' => '1', 'event_repeat_on_day' => '1', 'exdate' => '']);
        $values = $this->valuesFor(
            [[
                'pc_eid' => 8, 'pc_eventDate' => '2026-09-07', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '08:30:00',
                'pc_recurrtype' => 2, 'pc_recurrspec' => $spec,
            ]],
            [8 => ['2026-09-07 08:30:00', '2026-11-02 08:30:00', '2027-01-04 08:30:00']]
        );

        $this->assertSame(['next_appointment' => '2027-01-04 08:30', 'last_appointment' => '2026-11-02 08:30'], $values);
        $this->assertSame(['2026-11-13', '2027-01-14', 8], $this->expansions[0], 'two months and a day');
    }

    /**
     * More than 129 daily dates in a row excluded, on both sides of now: the windows follow
     * on from each other, so the search reaches past them in either direction.
     */
    public function testALongRunOfExcludedDailyDatesIsSearchedPast(): void
    {
        $dates = [];
        $at = new DateTimeImmutable('2026-01-01');
        while ($at->format('Y-m-d') <= '2027-12-31') {
            $day = $at->format('Y-m-d');
            // Excluded: 2026-06-01 through 2026-11-12 before now, 2026-11-13 through 2027-03-22 after.
            if ($day < '2026-06-01' || $day > '2027-03-22') {
                $dates[] = $day . ' 07:00:00';
            }
            $at = $at->modify('+1 day');
        }
        $values = $this->valuesFor(
            [[
                'pc_eid' => 2, 'pc_eventDate' => '2026-01-01', 'pc_endDate' => '2027-12-31', 'pc_startTime' => '07:00:00',
                'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 0),
            ]],
            [2 => $dates]
        );

        $this->assertSame(['next_appointment' => '2027-03-23 07:00', 'last_appointment' => '2026-05-31 07:00'], $values);
        foreach (array_slice($this->expansions, 1) as $i => [$from, $to]) {
            $previous = $this->expansions[$i];
            if ($previous[0] >= '2026-11-13' && $from >= '2026-11-13') {
                $this->assertGreaterThan($previous[1], $from, 'each forward window starts after the one before');
            }
        }
    }

    /**
     * A series with every date excluded is searched only up to the reach, ten years from
     * today, and then shows no date.
     */
    public function testTheSearchStopsAtItsReach(): void
    {
        $values = $this->valuesFor([[
            'pc_eid' => 1, 'pc_eventDate' => '2026-11-20', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '07:00:00',
            'pc_recurrtype' => 1, 'pc_recurrspec' => self::everySpec(1, 0),
        ]]);

        $this->assertSame([], $values);
        $last = end($this->expansions);
        $this->assertNotFalse($last);
        $this->assertSame('2036-11-13', $last[1], 'the last window ends at the reach');
        $this->assertLessThan(20, count($this->expansions));
    }

    /**
     * @return array<string, array{int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsupportedSpecProvider(): array
    {
        return [
            'every N in freq type 5, as older code stored it' => [1, self::everySpec(2, 5)],
            'every 0 days' => [1, self::everySpec(0, 0)],
            'no interval unit' => [1, serialize(['event_repeat_freq' => '1', 'exdate' => ''])],
            'not a spec' => [1, 'garbage'],
            'Nth weekday every 0 months' => [2, serialize(['event_repeat_on_freq' => '0', 'event_repeat_on_num' => '1', 'event_repeat_on_day' => '1'])],
            'days of the week with no days' => [3, self::everySpec(0, 6)],
            'an unknown recurrence type' => [7, self::everySpec(1, 1)],
        ];
    }

    /**
     * A recurrence the calendar cannot step through never reaches its expansion, where its
     * stepping would loop forever; the series shows no date.
     */
    #[DataProvider('unsupportedSpecProvider')]
    public function testAnUnsupportedRecurrenceIsNeverExpanded(int $recurrType, string $spec): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 7, 'pc_eventDate' => '2026-08-03', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '09:00:00',
                'pc_recurrtype' => $recurrType, 'pc_recurrspec' => $spec,
            ]],
            [7 => ['2026-11-16 09:00:00']]
        );

        $this->assertSame([], $values);
        $this->assertSame([], $this->expansions);
    }

    /**
     * A days-of-the-week series (type 3, freq type 6) is expanded week by week.
     */
    public function testADaysOfTheWeekSeriesIsExpanded(): void
    {
        $values = $this->valuesFor(
            [[
                'pc_eid' => 5, 'pc_eventDate' => '2026-09-01', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '16:00:00',
                'pc_recurrtype' => 3, 'pc_recurrspec' => serialize([
                    'event_repeat_freq' => '3,5', 'event_repeat_freq_type' => '6', 'exdate' => '',
                ]),
            ]],
            [5 => ['2026-11-10 16:00:00', '2026-11-12 16:00:00', '2026-11-17 16:00:00']]
        );

        $this->assertSame(['next_appointment' => '2026-11-17 16:00', 'last_appointment' => '2026-11-12 16:00'], $values);
    }

    /**
     * Only the asked-for columns are filled, and nothing is looked up when neither is asked.
     */
    public function testOnlyTheAskedForColumnsAreFilled(): void
    {
        $rows = [
            ['pc_eid' => 1, 'pc_eventDate' => '2026-10-01', 'pc_startTime' => '10:00:00', 'pc_recurrtype' => 0],
            ['pc_eid' => 2, 'pc_eventDate' => '2026-12-01', 'pc_startTime' => '10:00:00', 'pc_recurrtype' => 0],
        ];

        $this->assertSame(
            [7 => ['last_appointment' => '2026-10-01 10:00']],
            $this->appointments($rows)->values([7], [PatientFinderBuiltInColumn::LastAppointment])
        );
        $this->assertSame([], $this->appointments($rows)->values([7], [PatientFinderBuiltInColumn::Age]));
        $this->assertSame([], $this->appointments($rows)->values([0, -1], [PatientFinderBuiltInColumn::LastAppointment]));
    }

    /**
     * @return array<string, array{int, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function displayFormatProvider(): array
    {
        return [
            'ISO, 24 hr' => [0, 0, '2026-12-01 14:05'],
            'US, 12 hr' => [1, 1, '12/01/2026 2:05 PM'],
            'European, 24 hr' => [2, 0, '01/12/2026 14:05'],
            'unknown falls back to ISO, 24 hr' => [9, 9, '2026-12-01 14:05'],
        ];
    }

    /**
     * Times show in the site's date and time display formats.
     */
    #[DataProvider('displayFormatProvider')]
    public function testValuesUseTheSiteDisplayFormats(int $dateFormat, int $timeFormat, string $expected): void
    {
        $rows = [['pc_eid' => 1, 'pc_eventDate' => '2026-12-01', 'pc_startTime' => '14:05:00', 'pc_recurrtype' => 0]];

        $this->assertSame(
            [7 => ['next_appointment' => $expected]],
            $this->appointments($rows, [], $dateFormat, $timeFormat)->values([7], [PatientFinderBuiltInColumn::NextAppointment])
        );
    }
}
