<?php

/**
 * Isolated CalendarRenderDataBuilder Visit List Test
 *
 * The week, month and print views show Visit Lists as the day view does: a block's event
 * carries its list, and the patients on it are not also among the day's other events.
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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarRenderDataBuilderVisitListTest extends TestCase
{
    private const DATE = '2026-03-09';
    private const PROVIDER = 7;
    private const OTHER_PROVIDER = 8;
    private const FACILITY = 3;

    private bool $disableTranslation = false;

    protected function setUp(): void
    {
        // xl() returns its input, without a database, while translation is off.
        $this->disableTranslation = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->disableTranslation);
    }

    private static function builder(ViewType $viewType): CalendarRenderDataBuilder
    {
        return new CalendarRenderDataBuilder(new CalendarViewModel(viewType: $viewType, firstDayOfWeek: 0));
    }

    /**
     * Each provider's 9:00 to 12:00 block, with two patients on the first provider's list
     * (pc_eid 41 as #2 at stop 1, pc_eid 42 as #1) and one on the other's (pc_eid 51).
     */
    private static function schedule(): VisitListSchedule
    {
        $start = new DateTimeImmutable(self::DATE . ' 09:00');
        $end = new DateTimeImmutable(self::DATE . ' 12:00');
        return (new VisitListMatcher())->match(
            [
                new VisitListBlock(40, self::PROVIDER, self::FACILITY, $start, $end),
                new VisitListBlock(50, self::OTHER_PROVIDER, self::FACILITY, $start, $end),
            ],
            [
                new VisitListEntry(41, 541, self::PROVIDER, $start, 2, 1),
                new VisitListEntry(42, 542, self::PROVIDER, $start, 1, null),
                new VisitListEntry(51, 551, self::OTHER_PROVIDER, $start, 1, null),
            ]
        );
    }

    /**
     * The block events, their patients, and one timed appointment (pc_eid 60).
     *
     * @return list<array<string, mixed>>
     */
    private static function dayEvents(): array
    {
        return [
            self::event(40, self::PROVIDER, 'Visit List', 10800),
            self::event(41, self::PROVIDER, 'Established Patient', 0, 'Hollis, Margaret'),
            self::event(42, self::PROVIDER, 'Established Patient', 0, 'Kerr, Lee'),
            self::event(50, self::OTHER_PROVIDER, 'Visit List', 10800),
            self::event(51, self::OTHER_PROVIDER, 'Established Patient', 0, 'Moss, Ada'),
            self::event(60, self::PROVIDER, 'Office Visit', 1800, 'Park, Sam', '10:00:00'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function event(
        int $eid,
        int $aid,
        string $catname,
        int $duration,
        string $patientName = '',
        string $startTime = '09:00:00'
    ): array {
        return [
            'eid' => (string) $eid,
            'aid' => (string) $aid,
            'catid' => $catname === 'Visit List' ? '16' : '9',
            'catname' => $catname,
            'catcolor' => '#93d3a2',
            'pccattype' => $patientName === '' ? '1' : '0',
            'patient_name' => $patientName,
            'title' => $catname,
            'hometext' => '',
            'apptstatus' => '-',
            'startTime' => $startTime,
            'duration' => (string) $duration,
            'pid' => $patientName === '' ? '0' : '1',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function providers(): array
    {
        return [
            ['id' => (string) self::PROVIDER, 'fname' => 'Alex', 'lname' => 'Rivera', 'username' => 'arivera'],
            ['id' => (string) self::OTHER_PROVIDER, 'fname' => 'Bo', 'lname' => 'Chen', 'username' => 'bchen'],
        ];
    }

    /**
     * @return list<array{hour: int, minute: int, mer: string}>
     */
    private static function times(): array
    {
        return [
            ['hour' => 8, 'minute' => 0, 'mer' => 'am'],
            ['hour' => 12, 'minute' => 0, 'mer' => 'pm'],
            ['hour' => 17, 'minute' => 0, 'mer' => 'pm'],
        ];
    }

    /**
     * @return list<string>
     */
    private static function dayNames(): array
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function arrayAt(mixed $value, int|string ...$keys): array
    {
        foreach ($keys as $key) {
            self::assertIsArray($value);
            self::assertArrayHasKey($key, $value);
            $value = $value[$key];
        }
        self::assertIsArray($value);
        return $value;
    }

    /**
     * The eids of a day's decorated events, in order.
     *
     * @return list<string>
     */
    private static function eids(mixed $events): array
    {
        $eids = [];
        foreach (self::arrayAt($events) as $event) {
            $eid = self::arrayAt($event)['eid'] ?? null;
            self::assertTrue(is_string($eid) || is_int($eid));
            $eids[] = (string) $eid;
        }
        return $eids;
    }

    /**
     * The event with this eid among a day's decorated events.
     *
     * @return array<int|string, mixed>
     */
    private static function eventWith(mixed $events, string $eid): array
    {
        foreach (self::arrayAt($events) as $event) {
            $event = self::arrayAt($event);
            if ((string) ($event['eid'] ?? '') === $eid) {
                return $event;
            }
        }
        self::fail("No event {$eid}");
    }

    /**
     * Each row's list number, stop and patient name, in list order.
     *
     * @param array<int|string, mixed> $blockEvent
     * @return list<string>
     */
    private static function rows(array $blockEvent): array
    {
        self::assertSame('event_visit_list', $blockEvent['evtClass'] ?? null);
        $rows = [];
        foreach (self::arrayAt($blockEvent, 'visitList', 'rows') as $row) {
            $row = self::arrayAt($row);
            $sequence = $row['sequenceLabel'] ?? null;
            $stop = $row['stopLabel'] ?? null;
            $name = $row['patientName'] ?? null;
            self::assertIsString($sequence);
            self::assertIsString($stop);
            self::assertIsString($name);
            $rows[] = implode(' ', array_filter([$sequence, $stop, $name], static fn (string $part): bool => $part !== ''));
        }
        return $rows;
    }

    public function testMonthPrintShowsEachProvidersListInOrder(): void
    {
        $result = self::builder(ViewType::MonthPrint)->buildMonthPrintRenderData(
            [self::DATE => self::dayEvents()],
            self::providers(),
            '20260309',
            self::dayNames(),
            0,
            self::schedule(),
            true
        );

        $events = self::arrayAt($result, 'A_EVENTS', self::DATE);
        $this->assertSame(['40', '50', '60'], self::eids($events), 'every Visit List patient leaves the cell');
        $this->assertSame(['#1 Kerr, Lee', '#2 Stop 1 Hollis, Margaret'], self::rows(self::eventWith($events, '40')));
        $this->assertSame(['#1 Moss, Ada'], self::rows(self::eventWith($events, '50')));
        $this->assertSame('9:00am–12:00pm', self::arrayAt(self::eventWith($events, '40'), 'visitList')['timeLabel'] ?? null);
    }

    public function testMonthPrintWithoutVisitListsKeepsEveryEvent(): void
    {
        $result = self::builder(ViewType::MonthPrint)->buildMonthPrintRenderData(
            [self::DATE => self::dayEvents()],
            self::providers(),
            '20260309',
            self::dayNames(),
            0
        );

        $events = self::arrayAt($result, 'A_EVENTS', self::DATE);
        $this->assertSame(['40', '41', '42', '50', '51', '60'], self::eids($events));
        $this->assertArrayNotHasKey('visitList', self::eventWith($events, '40'));
    }

    public function testWeekPrintShowsTheProvidersListOnItsDay(): void
    {
        $result = self::builder(ViewType::WeekPrint)->buildWeekPrintRenderData(
            [self::DATE => self::dayEvents(), '2026-03-10' => []],
            self::providers(),
            '20260309',
            self::dayNames(),
            0,
            '/img',
            self::schedule(),
            true
        );

        $events = self::arrayAt($result, 'providers', 0, 'dayPairs', 0, 'left', 'events');
        $this->assertSame(['40', '60'], self::eids($events));
        $this->assertSame(['#1 Kerr, Lee', '#2 Stop 1 Hollis, Margaret'], self::rows(self::eventWith($events, '40')));
        $otherEvents = self::arrayAt($result, 'providers', 1, 'dayPairs', 0, 'left', 'events');
        $this->assertSame(['#1 Moss, Ada'], self::rows(self::eventWith($otherEvents, '50')));
    }

    public function testDayPrintShowsTheListInPlaceOfItsPatients(): void
    {
        $result = self::builder(ViewType::DayPrint)->buildDayPrintRenderData(
            [self::DATE => self::dayEvents()],
            self::providers(),
            self::times(),
            30,
            '20260309',
            self::dayNames(),
            0,
            '/img',
            true,
            self::schedule()
        );

        $events = self::arrayAt($result, 'providers', 0, 'events');
        $this->assertSame(['40', '60'], self::eids($events));
        $block = self::eventWith($events, '40');
        $this->assertSame(['#1 Kerr, Lee', '#2 Stop 1 Hollis, Margaret'], self::rows($block));
        $this->assertSame('120px', $block['evtHeight'] ?? null, 'the block keeps its time span');
    }

    public function testMonthScreenShowsTheListAndMarksHolidays(): void
    {
        $holiday = ['eid' => '70', 'aid' => '0', 'catid' => '6', 'catname' => 'Holiday', 'startTime' => '00:00:00', 'duration' => '0'];
        $result = $this->monthScreen([self::DATE => self::dayEvents(), '2026-03-10' => [$holiday]], 0);

        $days = self::arrayAt($result, 'providersGrid', 0, 'days');
        $events = self::arrayAt($days, 0, 'events');
        $this->assertSame(['40', '60'], self::eids($events));
        $this->assertSame(['#1 Kerr, Lee', '#2 Stop 1 Hollis, Margaret'], self::rows(self::eventWith($events, '40')));
        $this->assertFalse(self::arrayAt($days, 0)['isHolidayDay'] ?? null);
        $this->assertTrue(self::arrayAt($days, 1)['isHolidayDay'] ?? null);
    }

    public function testMonthScreenFilteredToAnotherFacilityKeepsThePatients(): void
    {
        $result = $this->monthScreen([self::DATE => self::dayEvents()], self::FACILITY + 1);

        $events = self::arrayAt($result, 'providersGrid', 0, 'days', 0, 'events');
        $this->assertSame(['40', '41', '42', '60'], self::eids($events));
        $this->assertArrayNotHasKey('visitList', self::eventWith($events, '40'));
    }

    public function testWeekScreenShowsTheListInItsColumn(): void
    {
        $result = self::builder(ViewType::Week)->buildWeekScreenRenderData(
            [self::DATE => self::dayEvents(), '2026-03-10' => []],
            self::providers(),
            self::providers(),
            [['id' => self::FACILITY, 'name' => 'Main']],
            self::times(),
            30,
            '20260309',
            self::dayNames(),
            0,
            0,
            '/img',
            '/openemr',
            '?prev',
            '?next',
            'fa-chevron-left',
            'fa-chevron-right',
            '',
            true,
            'Mar 09 - Mar 10',
            true,
            visitLists: self::schedule(),
            appointmentStatuses: ['-' => ['title' => '- None', 'color' => '#FEFDCF']]
        );

        $columns = self::arrayAt($result, 'providers', 0, 'dayColumns');
        $events = self::arrayAt($columns, 0, 'events');
        $this->assertSame(['40', '60'], self::eids($events));
        $block = self::eventWith($events, '40');
        $this->assertSame(['#1 Kerr, Lee', '#2 Stop 1 Hollis, Margaret'], self::rows($block));
        $this->assertSame('#FEFDCF', self::arrayAt($block, 'visitList', 'rows', 0)['bgColor'] ?? null);
        $this->assertSame([], self::eids(self::arrayAt($columns, 1, 'events')));
    }

    /**
     * @param array<string, list<array<string, mixed>>> $aEvents
     * @return array<string, mixed>
     */
    private function monthScreen(array $aEvents, int $pcFacility): array
    {
        return self::builder(ViewType::Month)->buildMonthScreenRenderData(
            $aEvents,
            self::providers(),
            self::providers(),
            [['id' => self::FACILITY, 'name' => 'Main']],
            '20260309',
            self::dayNames(),
            $pcFacility,
            0,
            '/img',
            '/openemr',
            '?prev',
            '?next',
            'fa-chevron-left',
            'fa-chevron-right',
            '',
            true,
            'March 2026',
            self::schedule(),
            ['-' => ['title' => '- None', 'color' => '#FEFDCF']],
            true
        );
    }
}
