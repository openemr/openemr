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

    /**
     * @param list<array<string, mixed>> $events
     */
    private static function column(array $events, ?VisitListSchedule $schedule, bool $twelveHour = true): VisitListDayColumn
    {
        return VisitListDayColumn::build(
            new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0),
            $events,
            $schedule,
            self::PROVIDER,
            self::DATE,
            self::DATE_YMD,
            self::STATUSES,
            $twelveHour
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
        $this->assertSame('9:00–5:00', $list['timeLabel']);
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
    }

    public function testPatientsStayInTheGridWhenTheirBlockIsNotInTheColumn(): void
    {
        $column = self::column(
            [self::patientEvent(41, 'Hollis, Margaret')],
            self::schedule([[41, 1, null]])
        );

        $this->assertSame(['41'], self::eids($column->gridEvents));
        $this->assertSame([], $column->lists);
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
}
