<?php

/**
 * Isolated VisitListDayColumn Test
 *
 * One provider's day-view column with Visit Lists: patients on a shown list leave the timed
 * grid, each list's rows carry list order, name, category and status, and patients whose
 * block is not in the column stay in the grid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar\ViewModel;

use DateTimeImmutable;
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListMatcher;
use OpenEMR\Appointment\VisitList\VisitListSchedule;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PostCalendar\ViewModel\CalendarRenderDataBuilder;
use OpenEMR\PostCalendar\ViewModel\CalendarViewModel;
use OpenEMR\PostCalendar\ViewModel\ViewType;
use OpenEMR\PostCalendar\ViewModel\VisitListDayColumn;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class VisitListDayColumnTest extends TestCase
{
    private const DATE = '2026-03-09';
    private const DATE_YMD = '20260309';
    private const PROVIDER = 7;

    /** @var array<string, array{title: string, color: string}> */
    private const STATUSES = [
        '-' => ['title' => '- None', 'color' => '#FEFDCF'],
        'AVM' => ['title' => 'AVM Confirmed', 'color' => '#F0FFE8'],
    ];

    private bool $disableTranslation = false;

    protected function setUp(): void
    {
        // xl() returns its input, without a database, while translation is off. Without this
        // the tests that draw a day needed another test to have turned translation off first.
        $this->disableTranslation = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->disableTranslation);
    }

    /**
     * @param list<array<string, mixed>> $events
     */
    private static function column(array $events, ?VisitListSchedule $schedule, bool $twelveHour = true, int $pcFacility = 0): VisitListDayColumn
    {
        return VisitListDayColumn::build(
            new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0),
            $events,
            $schedule,
            self::PROVIDER,
            self::DATE,
            self::DATE_YMD,
            self::STATUSES,
            $twelveHour,
            $pcFacility,
            15
        );
    }

    /**
     * A schedule with one 9:00 to 17:00 block (pc_eid 40) and the given patients on it.
     *
     * @param list<array{int, ?int, ?int}> $patients pc_eid, list order and stop of each patient
     */
    private static function schedule(array $patients, int $provider = self::PROVIDER): VisitListSchedule
    {
        $entries = array_map(
            static fn (array $patient): VisitListEntry => new VisitListEntry(
                $patient[0],
                500 + $patient[0],
                $provider,
                new DateTimeImmutable(self::DATE . ' 09:00'),
                $patient[1],
                $patient[2]
            ),
            $patients
        );
        return (new VisitListMatcher())->match(
            [new VisitListBlock(40, $provider, 3, new DateTimeImmutable(self::DATE . ' 09:00'), new DateTimeImmutable(self::DATE . ' 17:00'))],
            $entries
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function blockEvent(): array
    {
        return ['eid' => '40', 'catid' => '16', 'catname' => 'Visit List', 'pccattype' => '1', 'startTime' => '09:00:00'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function patientEvent(int $eid, string $name, string $status = '-', string $comment = ''): array
    {
        return [
            'eid' => (string) $eid,
            'catid' => '9',
            'catname' => 'Established Patient',
            'catcolor' => '#93d3a2',
            'pccattype' => '0',
            'patient_name' => $name,
            'apptstatus' => $status,
            'hometext' => $comment,
            'startTime' => '09:00:00',
        ];
    }

    /**
     * @param list<array<string, mixed>> $events
     * @return list<mixed>
     */
    private static function eids(array $events): array
    {
        return array_map(static fn (array $event): mixed => $event['eid'] ?? null, $events);
    }

    public function testWithoutVisitListsTheColumnIsUnchanged(): void
    {
        $events = [self::patientEvent(41, 'Hollis, Margaret')];

        $column = self::column($events, null);

        $this->assertSame($events, $column->gridEvents);
        $this->assertSame([], $column->lists);
    }

    public function testPatientsOnTheListLeaveTheGridAndBecomeRows(): void
    {
        $column = self::column(
            [self::blockEvent(), self::patientEvent(42, 'Varga, Peter'), self::patientEvent(41, 'Hollis, Margaret'), self::patientEvent(50, 'Timed, Tess')],
            self::schedule([[41, 1, null], [42, 2, 5]])
        );

        $this->assertSame(['40', '50'], self::eids($column->gridEvents));
        $list = $column->lists[40];
        $this->assertSame('Visit List', $list['title']);
        $this->assertSame('9:00am–5:00pm', $list['timeLabel']);
        $this->assertSame(self::PROVIDER, $list['providerId'], 'the + button adds to this provider');
        $this->assertSame(self::DATE_YMD, $list['eventDate']);
        $this->assertSame('9', $list['startHour'], '24-hour, as the popup reads it');
        $this->assertSame('00', $list['startMinute']);
        $this->assertSame(2, $list['count']);
        $this->assertSame([41, 42], array_column($list['rows'], 'eid'));
        $this->assertSame(['#1', '#2'], array_column($list['rows'], 'sequenceLabel'));
        $this->assertSame('Hollis, Margaret', $list['rows'][0]['patientName']);
        $this->assertSame('Established Patient', $list['rows'][0]['categoryName']);
        $this->assertSame(self::DATE_YMD, $list['rows'][0]['eventDate']);
        $this->assertSame('0', $list['rows'][0]['pccattype']);
    }

    public function testTwentyFourHourTimesWhenTheSiteUsesThem(): void
    {
        $column = self::column([self::blockEvent()], self::schedule([]), false);

        $this->assertSame('09:00–17:00', $column->lists[40]['timeLabel']);
    }

    public function testRowsAreColoredByStatusThenCategory(): void
    {
        $column = self::column(
            [self::blockEvent(), self::patientEvent(41, 'A, One', 'AVM'), self::patientEvent(42, 'B, Two', '-'), self::patientEvent(43, 'C, Three', '?')],
            self::schedule([[41, 1, null], [42, 2, null], [43, 3, null]])
        );

        $rows = $column->lists[40]['rows'];
        $this->assertSame('#F0FFE8', $rows[0]['bgColor']);
        $this->assertSame('AVM Confirmed', $rows[0]['statusTitle']);
        $this->assertSame('#FEFDCF', $rows[1]['bgColor'], 'no status still has the list color for it');
        $this->assertSame('', $rows[1]['statusTitle'], 'no status shows no status');
        $this->assertSame('#93d3a2', $rows[2]['bgColor'], 'a status the list does not have falls back to the category color');
        $this->assertSame('?', $rows[2]['statusTitle']);
    }

    public function testRowWithoutListOrderShowsJustTheMark(): void
    {
        $column = self::column([self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret')], self::schedule([[41, null, null]]));

        $this->assertSame('#', $column->lists[40]['rows'][0]['sequenceLabel']);
    }

    public function testTooltipHasStopNumberAndComment(): void
    {
        $column = self::column(
            [self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret', 'AVM', 'Gate code 1234')],
            self::schedule([[41, 1, 6]])
        );

        $tooltip = $column->lists[40]['rows'][0]['tooltip'];
        $this->assertStringContainsString("Hollis, Margaret\nEstablished Patient\nAVM Confirmed", $tooltip);
        $this->assertStringContainsString('Stop 6', $tooltip);
        $this->assertStringContainsString('Gate code 1234', $tooltip);
        $this->assertStringEndsWith('(double click to edit)', $tooltip, 'as on a timed appointment');
    }

    public function testRowNameOpensThePatientsChart(): void
    {
        $column = self::column([self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret')], self::schedule([[41, 1, null]]));

        $row = $column->lists[40]['rows'][0];
        $this->assertSame(541, $row['pid']);
        $this->assertSame("Hollis, Margaret\n(Click to view)", $row['patientTooltip']);
    }

    public function testPatientsStayInTheGridWhenTheirBlockIsNotInTheColumn(): void
    {
        $column = self::column(
            [self::patientEvent(41, 'Hollis, Margaret'), self::patientEvent(50, 'Timed, Tess')],
            self::schedule([[41, 1, null]])
        );

        $this->assertSame(['41', '50'], self::eids($column->gridEvents));
        $this->assertSame([], $column->lists);
        $this->assertSame(900, $column->gridEvents[0]['duration'], 'drawn one 15-minute slot tall, not 0');
        $this->assertArrayNotHasKey('duration', $column->gridEvents[1], 'other events are untouched');
    }

    public function testBlockAtAnotherFacilityIsNotShownWhenTheCalendarIsFilteredToOne(): void
    {
        $events = [self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret')];
        $schedule = self::schedule([[41, 1, null]]);

        $filtered = self::column($events, $schedule, true, 4);
        $this->assertSame([], $filtered->lists, 'the block is at facility 3');
        $this->assertSame(['40', '41'], self::eids($filtered->gridEvents));

        $this->assertArrayHasKey(40, self::column($events, $schedule, true, 3)->lists, 'its own facility');
        $this->assertArrayHasKey(40, self::column($events, $schedule, true, 0)->lists, 'all facilities');
    }

    public function testAnotherProvidersListIsNotShown(): void
    {
        $column = self::column(
            [self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret')],
            self::schedule([[41, 1, null]], 8)
        );

        $this->assertSame(['40', '41'], self::eids($column->gridEvents));
        $this->assertSame([], $column->lists);
    }

    public function testListPatientMissingFromTheColumnIsLeftOff(): void
    {
        $column = self::column([self::blockEvent()], self::schedule([[41, 1, null]]));

        $this->assertSame(0, $column->lists[40]['count']);
        $this->assertSame([], $column->lists[40]['rows']);
    }

    public function testAppointmentStatusesComeFromListRows(): void
    {
        $statuses = VisitListDayColumn::appointmentStatuses(
            [
                ['option_id' => '@', 'title' => '@ Arrived', 'notes' => 'FF2414|10'],
                ['option_id' => 'x', 'title' => 'x Canceled', 'notes' => 'red; background: url(x)|0'],
                ['option_id' => '', 'title' => 'Nothing', 'notes' => 'FFFFFF|0'],
                ['option_id' => 'n', 'title' => 'No notes'],
            ],
            static fn (string $title): string => 'translated ' . $title
        );

        $this->assertSame([
            '@' => ['title' => 'translated @ Arrived', 'color' => '#FF2414'],
            'x' => ['title' => 'translated x Canceled', 'color' => ''],
            'n' => ['title' => 'translated No notes', 'color' => ''],
        ], $statuses);
    }

    public function testBuilderShowsTheListOnTheBlockAndLeavesThePatientsOutOfTheGrid(): void
    {
        $builder = new CalendarRenderDataBuilder(new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0));
        $events = [
            self::DATE => [
                ['eid' => 40, 'aid' => self::PROVIDER, 'catid' => 16, 'catname' => 'Visit List', 'pccattype' => 1, 'startTime' => '09:00:00', 'duration' => 28800, 'hometext' => '', 'apptstatus' => '-'],
                ['eid' => 41, 'aid' => self::PROVIDER, 'catid' => 9, 'catname' => 'Established Patient', 'pccattype' => 0, 'startTime' => '09:00:00', 'duration' => 0, 'hometext' => '', 'apptstatus' => 'AVM', 'patient_name' => 'Hollis, Margaret', 'pid' => 541],
            ],
        ];

        $result = $builder->buildDayScreenRenderData(
            $events,
            [['id' => self::PROVIDER, 'fname' => 'Alex', 'lname' => 'Rivera', 'username' => 'arivera']],
            [],
            [['id' => 3, 'name' => 'Main']],
            [['hour' => 8, 'minute' => 0, 'mer' => 'am'], ['hour' => 17, 'minute' => 0, 'mer' => 'pm']],
            30,
            self::DATE_YMD,
            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            0,
            0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            true,
            '',
            true,
            self::schedule([[41, 1, null]]),
            self::STATUSES
        );

        $providers = $result['providers'];
        $this->assertIsArray($providers);
        $this->assertIsArray($providers[0]);
        $decorated = $providers[0]['events'];
        $this->assertIsArray($decorated);
        $this->assertCount(1, $decorated, 'the patient is on the list, not in the grid');
        $this->assertIsArray($decorated[0]);
        $this->assertSame(40, $decorated[0]['eid']);
        $this->assertSame('event_visit_list', $decorated[0]['evtClass']);
        $this->assertIsArray($decorated[0]['visitList']);
        $this->assertSame(1, $decorated[0]['visitList']['count']);
    }

    public function testOverlapOrderPutsTheBlocksThatShowAListLast(): void
    {
        $timed = self::patientEvent(42, 'Ruiz, Ana');
        $column = self::column([self::blockEvent(), self::patientEvent(41, 'Hollis, Margaret'), $timed], self::schedule([[41, 1, null]]));

        $this->assertSame(['40', '42'], self::eids($column->gridEvents), 'the grid keeps its order');
        $this->assertSame(['42', '40'], self::eids($column->overlapOrder(15)));
    }

    /**
     * The column of a day with two blocks, A (pc_eid 40) and B (pc_eid 45), and a 9:00
     * appointment (pc_eid 42) between them in the day's events.
     */
    private static function twoBlocks(string $aEnd, string $bStart, string $bEnd): VisitListDayColumn
    {
        $date = new DateTimeImmutable(self::DATE);
        $schedule = (new VisitListMatcher())->match([
            new VisitListBlock(40, self::PROVIDER, 3, $date->modify('09:00'), $date->modify($aEnd)),
            new VisitListBlock(45, self::PROVIDER, 3, $date->modify($bStart), $date->modify($bEnd)),
        ], []);
        $block = static fn (string $eid, string $start, string $end): array => [
            ...self::blockEvent(),
            'eid' => $eid,
            'startTime' => $start . ':00',
            'duration' => (string) ((new DateTimeImmutable(self::DATE . ' ' . $end))->getTimestamp() - (new DateTimeImmutable(self::DATE . ' ' . $start))->getTimestamp()),
        ];
        return self::column([$block('40', '09:00', $aEnd), self::patientEvent(42, 'Ruiz, Ana'), $block('45', $bStart, $bEnd)], $schedule);
    }

    public function testOverlapOrderMovesBlocksThatFollowEachOther(): void
    {
        $this->assertSame(['42', '40', '45'], self::eids(self::twoBlocks('10:00', '10:00', '11:00')->overlapOrder(15)));
    }

    public function testOverlapOrderKeepsTheOrderWhenBlocksOverlap(): void
    {
        // Moved last, A would take B's place in the 10:00 slot and keep it.
        $this->assertSame(['40', '42', '45'], self::eids(self::twoBlocks('11:00', '10:00', '11:00')->overlapOrder(15)));
    }

    public function testAppointmentsBesideABlockAreDrawnToItsLeft(): void
    {
        $places = self::places([
            // As in the day's event list: the 9:00 appointment before the block that starts with it.
            self::timed(42, '09:00:00', 1800),
            self::block(40, '09:00:00', 7200),
            ['eid' => 41, 'aid' => self::PROVIDER, 'catid' => 9, 'catname' => 'Established Patient', 'pccattype' => 0, 'startTime' => '09:00:00', 'duration' => 0, 'hometext' => '', 'apptstatus' => '-', 'patient_name' => 'Hollis, Margaret', 'pid' => 541],
            self::timed(43, '09:30:00', 1800),
            self::timed(44, '10:00:00', 1800),
        ], self::schedule([[41, 1, null]]), 30);

        $this->assertSame([
            40 => ['left: 50%', 'width: 50%'],
            42 => ['left: 0%', 'width: 50%'],
            43 => ['left: 0%', 'width: 50%'],
            44 => ['left: 0%', 'width: 50%'],
        ], $places);
    }

    public function testBlocksThatOverlapEachOtherAreDrawnAsOnMaster(): void
    {
        // A 9:00-11:00 and B 10:00-11:00 overlap, so the day's own order is kept, and the
        // calendar's layout places them as it did before Visit Lists were moved: A takes the
        // right half beside the 9:00 appointment and keeps it, and B gets the right half beside
        // A in the 10:00 slot. The layout keeps each event's rightmost place across its slots,
        // so two overlapping blocks can share a half; this pins that unchanged behavior.
        $places = self::places([
            self::timed(42, '09:00:00', 1800),
            self::block(40, '09:00:00', 7200),
            self::block(45, '10:00:00', 3600),
        ], self::twoBlockSchedule('09:00', '11:00', '10:00', '11:00'), 30);

        $this->assertSame([
            40 => ['left: 50%', 'width: 50%'],
            42 => ['left: 0%', 'width: 50%'],
            45 => ['left: 50%', 'width: 50%'],
        ], $places);
    }

    public function testBlocksMeetingInsideASlotAreDrawnSideBySide(): void
    {
        // A ends at 10:15 and B starts at 10:15: with 30-minute slots both are in the 10:00
        // slot, so they count as overlapping and keep the day's order, side by side.
        $places = self::places([
            self::block(40, '09:00:00', 4500),
            self::timed(42, '09:15:00', 1800),
            self::block(45, '10:15:00', 2700),
        ], self::twoBlockSchedule('09:00', '10:15', '10:15', '11:00'), 30);

        $this->assertSame([
            40 => ['left: 0%', 'width: 50%'],
            42 => ['left: 50%', 'width: 50%'],
            45 => ['left: 50%', 'width: 50%'],
        ], $places);
    }

    public function testAnAppointmentRunningPastABlockIsNotDrawnOverIt(): void
    {
        // A 8:00-11:00 and C 9:00-11:00 run past block B 9:00-10:00. With B moved last, C
        // would take the right half in the 10:00 slot and keep it at a third of the width,
        // covering part of B, so the day's own order is used: three thirds side by side.
        $places = self::places([
            self::timed(42, '08:00:00', 10800),
            self::block(40, '09:00:00', 3600),
            self::timed(43, '09:00:00', 7200),
        ], self::twoBlockSchedule('09:00', '10:00', '13:00', '14:00'), 30);

        $this->assertSame([
            40 => ['left: 33.333333333333%', 'width: 33.333333333333%'],
            42 => ['left: 0%', 'width: 33.333333333333%'],
            43 => ['left: 66.666666666667%', 'width: 33.333333333333%'],
        ], $places);
    }

    public function testWhenBothOrdersDrawAnOverlapTheDaysOrderIsKept(): void
    {
        // Block B 9:00-10:00, C 9:00-10:30 and D 9:30-10:30. The day's order draws D over C,
        // B moved last draws D over B: one overlap each, so the order is left as on master,
        // where the overlap stays between the two appointments and the block is clear.
        $places = self::places([
            self::block(40, '09:00:00', 3600),
            self::timed(42, '09:00:00', 5400),
            self::timed(43, '09:30:00', 3600),
        ], self::twoBlockSchedule('09:00', '10:00', '13:00', '14:00'), 30);

        $this->assertSame([
            40 => ['left: 0%', 'width: 33.333333333333%'],
            42 => ['left: 50%', 'width: 33.333333333333%'],
            43 => ['left: 66.666666666667%', 'width: 33.333333333333%'],
        ], $places);
    }

    public function testAReorderThatDrawsAnAppointmentOverTheBlockIsNotTaken(): void
    {
        // Block B 9:00-10:00, A 9:00-11:00, C 9:30-10:30, D 10:30-11:00. The day's order draws
        // C and D over A; B moved last has one overlap, C over B. Fewer overlaps, but on the
        // block, so the day's order is kept and B stays clear.
        $places = self::places([
            self::block(40, '09:00:00', 3600),
            self::timed(42, '09:00:00', 7200),
            self::timed(43, '09:30:00', 3600),
            self::timed(44, '10:30:00', 1800),
        ], self::twoBlockSchedule('09:00', '10:00', '13:00', '14:00'), 30);

        $this->assertSame('left: 0%', $places[40][0]);
        $this->assertSame('width: 33.333333333333%', $places[40][1]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function timed(int $eid, string $start, int $seconds): array
    {
        return [
            'eid' => $eid, 'aid' => self::PROVIDER, 'catid' => 9, 'catname' => 'Established Patient', 'pccattype' => 0,
            'startTime' => $start, 'duration' => $seconds, 'hometext' => '', 'apptstatus' => '-', 'patient_name' => 'Ruiz, Ana', 'pid' => 500 + $eid,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function block(int $eid, string $start, int $seconds): array
    {
        return ['eid' => $eid, 'aid' => self::PROVIDER, 'catid' => 16, 'catname' => 'Visit List', 'pccattype' => 1, 'startTime' => $start, 'duration' => $seconds, 'hometext' => '', 'apptstatus' => '-'];
    }

    private static function twoBlockSchedule(string $aStart, string $aEnd, string $bStart, string $bEnd): VisitListSchedule
    {
        $date = new DateTimeImmutable(self::DATE);
        return (new VisitListMatcher())->match([
            new VisitListBlock(40, self::PROVIDER, 3, $date->modify($aStart), $date->modify($aEnd)),
            new VisitListBlock(45, self::PROVIDER, 3, $date->modify($bStart), $date->modify($bEnd)),
        ], []);
    }

    /**
     * Where the day view draws each event in the grid: its left and width, by pc_eid.
     *
     * @param list<array<string, mixed>> $dayEvents
     * @return array<int, array{mixed, mixed}>
     */
    private static function places(array $dayEvents, VisitListSchedule $schedule, int $intervalMinutes): array
    {
        $builder = new CalendarRenderDataBuilder(new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0));
        $slots = [];
        for ($minutes = 8 * 60; $minutes < 12 * 60; $minutes += $intervalMinutes) {
            $slots[] = ['hour' => intdiv($minutes, 60), 'minute' => $minutes % 60, 'mer' => 'am'];
        }

        $result = $builder->buildDayScreenRenderData(
            [self::DATE => $dayEvents],
            [['id' => self::PROVIDER, 'fname' => 'Alex', 'lname' => 'Rivera', 'username' => 'arivera']],
            [],
            [['id' => 3, 'name' => 'Main']],
            $slots,
            $intervalMinutes,
            self::DATE_YMD,
            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            0,
            0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            true,
            '',
            true,
            $schedule,
            self::STATUSES
        );

        $providers = $result['providers'];
        self::assertIsArray($providers);
        self::assertIsArray($providers[0]);
        $decorated = $providers[0]['events'];
        self::assertIsArray($decorated);
        $places = [];
        foreach ($decorated as $event) {
            self::assertIsArray($event);
            self::assertIsInt($event['eid']);
            $places[$event['eid']] = [$event['divLeftCss'], $event['divWidthCss']];
        }
        ksort($places);
        return $places;
    }
}
