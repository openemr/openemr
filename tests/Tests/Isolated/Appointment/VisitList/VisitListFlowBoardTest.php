<?php

/**
 * Isolated VisitListFlowBoard Test
 *
 * Ordering the Flow Board's rows: Visit List patients in list order after the timed
 * appointments at their block's start, their Order # label and the block's time range.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\VisitList;

use DateTimeImmutable;
use OpenEMR\Appointment\VisitList\VisitList;
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListFlowBoard;
use OpenEMR\Appointment\VisitList\VisitListSchedule;
use OpenEMR\Appointment\VisitList\VisitListService;
use PHPUnit\Framework\TestCase;

class VisitListFlowBoardTest extends TestCase
{
    private const DATE = '2026-03-09';

    /**
     * A board row as fetchAppointments() gives it.
     *
     * @return array<string, mixed>
     */
    private static function row(int $eid, int $provider, string $time, string $date = self::DATE): array
    {
        return [
            'pc_eid' => (string) $eid,
            'uprovider_id' => (string) $provider,
            'pc_eventDate' => $date,
            'pc_startTime' => $time,
        ];
    }

    private static function block(int $eid, int $provider, string $start, string $end): VisitListBlock
    {
        return new VisitListBlock(
            $eid,
            $provider,
            3,
            new DateTimeImmutable(self::DATE . ' ' . $start),
            new DateTimeImmutable(self::DATE . ' ' . $end)
        );
    }

    private static function entry(int $eid, int $provider, string $start, ?int $sequence): VisitListEntry
    {
        return new VisitListEntry($eid, 500 + $eid, $provider, new DateTimeImmutable(self::DATE . ' ' . $start), $sequence, null);
    }

    /**
     * @param list<array<mixed>> $rows
     * @return list<string>
     */
    private static function eids(array $rows): array
    {
        $eids = [];
        foreach ($rows as $row) {
            self::assertIsString($row['pc_eid']);
            $eids[] = $row['pc_eid'];
        }
        return $eids;
    }

    /**
     * Provider 7's 9:00–11:00 block with patients 21 (#1), 22 (#2) and 23 (#3).
     */
    private static function schedule(): VisitListSchedule
    {
        return new VisitListSchedule([
            new VisitList(self::block(100, 7, '09:00', '11:00'), [
                self::entry(21, 7, '09:00', 1),
                self::entry(22, 7, '09:00', 2),
                self::entry(23, 7, '09:00', 3),
            ]),
        ], []);
    }

    public function testPatientsFollowTheTimedAppointmentsAtTheBlockStartInListOrder(): void
    {
        // As sortAppointments() leaves them: by time, then category and patient name.
        $rows = [
            self::row(10, 7, '08:30:00'),
            self::row(23, 7, '09:00:00'),
            self::row(11, 7, '09:00:00'),
            self::row(21, 7, '09:00:00'),
            self::row(22, 7, '09:00:00'),
            self::row(12, 7, '09:15:00'),
        ];

        $ordered = VisitListFlowBoard::order($rows, self::schedule(), false);

        $this->assertSame(['10', '11', '21', '22', '23', '12'], self::eids($ordered));
    }

    public function testListRowsGetTheirOrderNumberAndTheBlockTimes(): void
    {
        $ordered = VisitListFlowBoard::order(
            [self::row(11, 7, '09:00:00'), self::row(22, 7, '09:00:00')],
            self::schedule(),
            false
        );

        $this->assertArrayNotHasKey('visit_list', $ordered[0]);
        $this->assertSame(['block' => 100, 'sequenceLabel' => '#2', 'start' => '09:00', 'timeRange' => '09:00–11:00'], $ordered[1]['visit_list']);
    }

    public function testTwelveHourTimesAndAPatientWithoutAnOrderNumber(): void
    {
        $schedule = new VisitListSchedule([
            new VisitList(self::block(100, 7, '11:30', '13:00'), [self::entry(21, 7, '11:30', null)]),
        ], []);

        $ordered = VisitListFlowBoard::order([self::row(21, 7, '11:30:00')], $schedule, true);

        $this->assertSame(['block' => 100, 'sequenceLabel' => '#', 'start' => '11:30 am', 'timeRange' => '11:30 am–1:00 pm'], $ordered[0]['visit_list']);
    }

    public function testBlocksAtTheSameTimeKeepTheScheduleOrder(): void
    {
        $schedule = new VisitListSchedule([
            new VisitList(self::block(100, 7, '09:00', '11:00'), [self::entry(21, 7, '09:00', 1)]),
            new VisitList(self::block(101, 8, '09:00', '11:00'), [
                self::entry(31, 8, '09:00', 1),
                self::entry(32, 8, '09:00', 2),
            ]),
        ], []);
        $rows = [
            self::row(32, 8, '09:00:00'),
            self::row(31, 8, '09:00:00'),
            self::row(21, 7, '09:00:00'),
        ];

        $this->assertSame(['21', '31', '32'], self::eids(VisitListFlowBoard::order($rows, $schedule, false)));
    }

    public function testAPatientStoredInsideTheBlockMovesToTheBlockStart(): void
    {
        $schedule = new VisitListSchedule([
            new VisitList(self::block(100, 7, '09:00', '11:00'), [self::entry(21, 7, '09:30', 1)]),
        ], []);
        $rows = [self::row(11, 7, '09:15:00'), self::row(21, 7, '09:30:00')];

        $this->assertSame(['21', '11'], self::eids(VisitListFlowBoard::order($rows, $schedule, false)));
    }

    public function testRowsOffTheListStayAtTheirOwnDateAndTime(): void
    {
        // Patient 21's event on another date or provider, an unplaced patient, and rows
        // fetchAppointments() should never give.
        $schedule = new VisitListSchedule(self::schedule()->lists, [self::entry(40, 7, '10:30', 1)]);
        $rows = [
            self::row(21, 7, '09:00:00', '2026-03-10'),
            self::row(21, 9, '09:00:00'),
            self::row(40, 7, '10:30:00'),
            ['pc_eid' => 'x'],
        ];

        $ordered = VisitListFlowBoard::order($rows, $schedule, false);

        $this->assertSame([$rows[3], $rows[1], $rows[2], $rows[0]], $ordered);
    }

    public function testArrangeReadsTheRowsProvidersForTheDates(): void
    {
        $calls = [];
        $service = new VisitListService(
            static function (string $sql, array $binds): array {
                if (str_contains($sql, 'openemr_postcalendar_categories')) {
                    return [['pc_catid' => '16']];
                }
                return [[
                    'pc_eid' => '21', 'pc_pid' => '521', 'pc_aid' => '7', 'pc_eventDate' => self::DATE,
                    'pc_startTime' => '09:00:00', 'pc_visit_seq' => '1', 'pc_visit_stop' => null,
                ]];
            },
            static function (string $from, string $to, string $where, array $binds) use (&$calls): array {
                $calls[] = [$from, $to, $binds];
                return [[
                    'pc_eid' => '100', 'uprovider_id' => '7', 'pc_eventDate' => self::DATE,
                    'pc_startTime' => '09:00:00', 'pc_duration' => '7200', 'pc_facility' => '3',
                ]];
            }
        );
        $rows = [
            'a' => self::row(21, 7, '09:00:00'),
            'b' => self::row(11, 7, '09:00:00'),
            'c' => self::row(12, 8, '10:00:00', '2026-03-10'),
            'd' => 'not a row',
        ];

        $ordered = (new VisitListFlowBoard($service))->arrange($rows, false);

        $this->assertSame([[self::DATE, '2026-03-10', [16, '7', '8']]], $calls);
        $this->assertSame(['11', '21', '12'], self::eids($ordered));
    }

    public function testArrangeLeavesTheRowsWhenThereIsNothingToRead(): void
    {
        $service = new VisitListService(
            static fn (string $sql, array $binds): array => self::fail('no query expected'),
            static fn (string $from, string $to, string $where, array $binds): array => self::fail('no query expected')
        );
        $board = new VisitListFlowBoard($service);
        // Rows without a provider or a readable date.
        $rows = [
            'a' => ['pc_eid' => '21', 'pc_eventDate' => self::DATE],
            'b' => self::row(22, 7, '09:00:00', 'not a date'),
        ];

        $this->assertSame([], $board->arrange([], false));
        $this->assertSame([], $board->arrange(['not a row'], false));
        $this->assertSame([$rows['a'], $rows['b']], $board->arrange($rows, false));
    }
}
