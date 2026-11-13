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
                return array_map(static function (string $at): array {
                    [$date, $time] = explode(' ', $at);
                    return ['pc_eventDate' => $date, 'pc_startTime' => $time];
                }, $series[$eid] ?? []);
            },
            new FrozenClock(new DateTimeImmutable(self::NOW)),
            $dateFormat,
            $timeFormat
        );
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
     * date is last week, not the series' start.
     */
    public function testARunningSeriesCountsItsNearestDates(): void
    {
        $values = $this->valuesFor(
            [['pc_eid' => 9, 'pc_eventDate' => '2026-08-03', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '09:00:00', 'pc_recurrtype' => 1]],
            [9 => ['2026-08-03 09:00:00', '2026-11-09 09:00:00', '2026-11-16 09:00:00', '2026-11-23 09:00:00']]
        );

        $this->assertSame(['next_appointment' => '2026-11-16 09:00', 'last_appointment' => '2026-11-09 09:00'], $values);
        // From its start (later than a year before now) to a year and a day after now.
        $this->assertSame([['2026-08-03', '2027-11-14', 9]], $this->expansions);
    }

    /**
     * A series that ended years ago is expanded over the year before its end, where its
     * last date is.
     */
    public function testASeriesThatEndedLongAgoStillGivesItsLastDate(): void
    {
        $values = $this->valuesFor(
            [['pc_eid' => 5, 'pc_eventDate' => '2020-01-06', 'pc_endDate' => '2023-06-30', 'pc_startTime' => '11:00:00', 'pc_recurrtype' => 1]],
            [5 => ['2023-06-26 11:00:00']]
        );

        $this->assertSame(['last_appointment' => '2023-06-26 11:00'], $values);
        $this->assertSame([['2022-06-29', '2023-06-30', 5]], $this->expansions);
    }

    /**
     * A series that starts beyond the window is not expanded: its first date is its next.
     */
    public function testASeriesStartingBeyondTheWindowCountsItsFirstDate(): void
    {
        $values = $this->valuesFor([
            ['pc_eid' => 6, 'pc_eventDate' => '2028-02-01', 'pc_endDate' => '0000-00-00', 'pc_startTime' => '15:00:00', 'pc_recurrtype' => 2],
        ]);

        $this->assertSame(['next_appointment' => '2028-02-01 15:00'], $values);
        $this->assertSame([], $this->expansions);
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
