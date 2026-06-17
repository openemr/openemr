<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar\ViewModel;

use OpenEMR\PostCalendar\ViewModel\CalendarRenderDataBuilder;
use OpenEMR\PostCalendar\ViewModel\CalendarViewModel;
use OpenEMR\PostCalendar\ViewModel\ViewType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarRenderDataBuilderTest extends TestCase
{
    private function builder(ViewType $viewType, int $firstDayOfWeek = 0): CalendarRenderDataBuilder
    {
        return new CalendarRenderDataBuilder(
            new CalendarViewModel(viewType: $viewType, firstDayOfWeek: $firstDayOfWeek)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makeProvider(int $id = 1, string $fname = 'Alice', string $lname = 'Smith', string $username = 'asmith'): array
    {
        return ['id' => $id, 'fname' => $fname, 'lname' => $lname, 'username' => $username];
    }

    /**
     * @return array<string, mixed>
     */
    private function makeEvent(
        int $catid = 5,
        int $eid = 100,
        int $aid = 1,
        string $startTime = '09:00:00',
        int $duration = 1800
    ): array {
        return [
            'eid'        => $eid,
            'aid'        => $aid,
            'catid'      => $catid,
            'catname'    => 'Office Visit',
            'pccattype'  => 1,
            'startTime'  => $startTime,
            'duration'   => $duration,
            'title'      => 'Test Appointment',
            'hometext'   => '',
            'fname'      => 'Patient',
            'lname'      => 'One',
            'pid'        => 0,
            'apptstatus' => '-',
            'pc_facility' => 1,
        ];
    }

    /**
     * @return list<array{hour: int, minute: int, mer: string}>
     */
    private function makeTimes(): array
    {
        return [
            ['hour' => 8,  'minute' => 0,  'mer' => 'am'],
            ['hour' => 8,  'minute' => 30, 'mer' => 'am'],
            ['hour' => 9,  'minute' => 0,  'mer' => 'am'],
            ['hour' => 9,  'minute' => 30, 'mer' => 'am'],
            ['hour' => 17, 'minute' => 0,  'mer' => 'pm'],
        ];
    }

    /**
     * @return list<string>
     */
    private function shortDayNames(): array
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }

    /**
     * Narrow a mixed key on a result array to a concrete array shape for
     * downstream assertions, in a single call. Standardising on this
     * keeps the offset-access narrowing inline-readable.
     *
     * @param  array<string, mixed> $result
     * @return array<int|string, mixed>
     */
    private function arrayAt(array $result, string $key): array
    {
        self::assertArrayHasKey($key, $result);
        $value = $result[$key];
        self::assertIsArray($value);
        return $value;
    }

    public function testConstructorExposesViewModelAsPublicReadonlyProperty(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);
        $builder = new CalendarRenderDataBuilder($vm);
        self::assertSame($vm, $builder->viewModel);
    }

    public function testBuildMonthPrintRenderDataReturnsExpectedTopLevelKeys(): void
    {
        $builder = $this->builder(ViewType::MonthPrint);
        $events = [
            '2026-03-15' => [$this->makeEvent()],
            '2026-03-16' => [],
        ];

        $result = $builder->buildMonthPrintRenderData(
            $events,
            [$this->makeProvider()],
            '20260315',
            $this->shortDayNames(),
            0
        );

        self::assertArrayHasKey('providers', $result);
        self::assertArrayHasKey('dowList', $result);
        self::assertArrayHasKey('A_SHORT_DAY_NAMES', $result);
        self::assertArrayHasKey('dateLabel', $result);
        self::assertArrayHasKey('currentMonthMini', $result);
        self::assertArrayHasKey('nextMonthMini', $result);
        self::assertArrayHasKey('A_EVENTS', $result);
        self::assertArrayHasKey('dowOfDate', $result);
        self::assertSame('March 2026', $result['dateLabel']);
    }

    public function testBuildMonthPrintFiltersOutCatids2And3(): void
    {
        // catid 2 (IN event) and catid 3 (OUT event) are clinic-window
        // markers — month_print's template iterates the visible events
        // only, so the builder strips them from A_EVENTS before render.
        $builder = $this->builder(ViewType::MonthPrint);
        $events = [
            '2026-03-15' => [
                $this->makeEvent(catid: 2, eid: 1),
                $this->makeEvent(catid: 5, eid: 2),
                $this->makeEvent(catid: 3, eid: 3),
            ],
        ];

        $result = $builder->buildMonthPrintRenderData(
            $events,
            [$this->makeProvider()],
            '20260315',
            $this->shortDayNames(),
            0
        );

        $allEvents = $this->arrayAt($result, 'A_EVENTS');
        self::assertArrayHasKey('2026-03-15', $allEvents);
        $dayEvents = $allEvents['2026-03-15'];
        self::assertIsArray($dayEvents);
        self::assertCount(1, $dayEvents);
        $firstEvent = $dayEvents[0];
        self::assertIsArray($firstEvent);
        self::assertSame(2, $firstEvent['eid']);
    }

    public function testBuildMonthPrintWrapsMonthBoundaryForNextMini(): void
    {
        $builder = $this->builder(ViewType::MonthPrint);

        $result = $builder->buildMonthPrintRenderData(
            ['2026-12-15' => []],
            [],
            '20261215',
            $this->shortDayNames(),
            0
        );

        // nextMonthMini should wrap to January (2027).
        $nextMini = $this->arrayAt($result, 'nextMonthMini');
        self::assertSame('January', $nextMini['monthLabel']);
        self::assertSame(2027, $nextMini['year']);
    }

    public function testBuildWeekPrintRenderDataReturnsExpectedTopLevelKeys(): void
    {
        $builder = $this->builder(ViewType::WeekPrint);
        $events = [
            '2026-03-15' => [$this->makeEvent()],
            '2026-03-16' => [],
            '2026-03-17' => [],
            '2026-03-18' => [],
            '2026-03-19' => [$this->makeEvent(eid: 101)],
            '2026-03-20' => [],
            '2026-03-21' => [],
        ];

        $result = $builder->buildWeekPrintRenderData(
            $events,
            [$this->makeProvider()],
            '20260315',
            $this->shortDayNames(),
            0,
            '/img'
        );

        self::assertArrayHasKey('providers', $result);
        self::assertArrayHasKey('dateRange', $result);
        self::assertArrayHasKey('currentMonthMini', $result);
        self::assertArrayHasKey('nextMonthMini', $result);

        $dateRange = $this->arrayAt($result, 'dateRange');
        self::assertSame('March', $dateRange['firstMonth']);
        self::assertSame('15', $dateRange['firstDay']);
        self::assertSame('21', $dateRange['lastDay']);

        $providers = $this->arrayAt($result, 'providers');
        self::assertCount(1, $providers);
        self::assertIsArray($providers[0]);
        self::assertSame(1, $providers[0]['id']);
    }

    public function testBuildWeekPrintPairsDayWithDayPlusFour(): void
    {
        $builder = $this->builder(ViewType::WeekPrint);
        // Week-print's per-provider grid pairs day N (left column) with
        // day N+4 (right column). For a 7-day input the first three
        // pairs hit valid right columns; the 4th has no day+4 in range
        // and gets right=null.
        $events = [
            '2026-03-15' => [],
            '2026-03-16' => [],
            '2026-03-17' => [],
            '2026-03-18' => [],
            '2026-03-19' => [],
            '2026-03-20' => [],
            '2026-03-21' => [],
        ];

        $result = $builder->buildWeekPrintRenderData(
            $events,
            [$this->makeProvider()],
            '20260315',
            $this->shortDayNames(),
            0,
            '/img'
        );

        $providers = $this->arrayAt($result, 'providers');
        self::assertIsArray($providers[0]);
        $pairs = $providers[0]['dayPairs'];
        self::assertIsArray($pairs);
        self::assertCount(4, $pairs); // legacy loop caps at 4
        self::assertIsArray($pairs[0]);
        self::assertIsArray($pairs[1]);
        self::assertIsArray($pairs[2]);
        self::assertIsArray($pairs[3]);
        self::assertNotNull($pairs[0]['right']);
        self::assertNotNull($pairs[1]['right']);
        self::assertNotNull($pairs[2]['right']);
        self::assertNull($pairs[3]['right']); // day+4 falls outside the 7-day window
    }

    public function testBuildDayPrintRenderDataReturnsExpectedTopLevelKeys(): void
    {
        $builder = $this->builder(ViewType::DayPrint);
        $events = [
            '2026-03-15' => [$this->makeEvent(startTime: '09:00:00')],
        ];

        $result = $builder->buildDayPrintRenderData(
            $events,
            [$this->makeProvider()],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            0,
            '/img',
            true
        );

        self::assertArrayHasKey('providers', $result);
        self::assertArrayHasKey('dateHeader', $result);
        self::assertArrayHasKey('timeRows', $result);
        self::assertArrayHasKey('timeslotCss', $result);
        self::assertSame('20px', $result['timeslotCss']);

        $dateHeader = $this->arrayAt($result, 'dateHeader');
        self::assertSame('Sunday', $dateHeader['weekdayLabel']);

        $timeRows = $this->arrayAt($result, 'timeRows');
        self::assertCount(5, $timeRows);
    }

    public function testBuildDayPrintProviderEventsAreFilteredByAid(): void
    {
        // aid=0 means "global" — visible to every provider's column.
        // aid=N means "provider N only". Provider 99 sees aid=0 and
        // aid=99 events; not aid=1.
        $builder = $this->builder(ViewType::DayPrint);
        $events = [
            '2026-03-15' => [
                $this->makeEvent(eid: 10, aid: 0,  startTime: '09:00:00'),
                $this->makeEvent(eid: 11, aid: 99, startTime: '09:30:00'),
                $this->makeEvent(eid: 12, aid: 1,  startTime: '10:00:00'),
            ],
        ];

        $result = $builder->buildDayPrintRenderData(
            $events,
            [$this->makeProvider(id: 99, username: 'p99')],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            0,
            '/img',
            true
        );

        $providers = $this->arrayAt($result, 'providers');
        self::assertIsArray($providers[0]);
        $providerEvents = $providers[0]['events'];
        self::assertIsArray($providerEvents);
        self::assertSame([10, 11], array_column($providerEvents, 'eid'));
    }

    public function testBuildMonthScreenRenderDataReturnsExpectedTopLevelKeys(): void
    {
        // Screen builders' per-event decoration calls dateformat() (via
        // addI18nDateDecoration), which needs a DB. Use empty events
        // here — key-shape assertions don't need actual events.
        $builder = $this->builder(ViewType::Month);
        $events = ['2026-03-15' => []];

        $result = $builder->buildMonthScreenRenderData(
            $events,
            [$this->makeProvider()],
            [$this->makeProvider()],
            [['id' => 1, 'name' => 'Main', 'color' => '#fff']],
            '20260315',
            $this->shortDayNames(),
            1,
            0,
            '/img',
            '/openemr',
            '?prev',
            '?next',
            'fa-chevron-left',
            'fa-chevron-right',
            '<select id="monthPicker"></select>',
            true,
            'March 2026'
        );

        self::assertSame('month', $result['viewtype']);
        self::assertSame('20260315', $result['Date']);
        self::assertSame('March 2026', $result['currentMonthLabel']);
        self::assertSame('?prev', $result['PREV_MONTH_URL']);
        self::assertSame('?next', $result['NEXT_MONTH_URL']);
        self::assertArrayHasKey('currentMiniCal', $result);
        self::assertArrayHasKey('providersGrid', $result);
    }

    public function testBuildDayScreenRenderDataReturnsExpectedTopLevelKeys(): void
    {
        // See note on the month-screen test re: empty events.
        $builder = $this->builder(ViewType::Day);
        $events = ['2026-03-15' => []];

        $result = $builder->buildDayScreenRenderData(
            $events,
            [$this->makeProvider()],
            [$this->makeProvider()],
            [['id' => 1, 'name' => 'Main']],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            1,
            0,
            '/img',
            '/openemr',
            '?prev',
            '?next',
            'fa-chevron-left',
            'fa-chevron-right',
            '<select id="monthPicker"></select>',
            true,
            'Sunday, March 15, 2026',
            true
        );

        self::assertSame('day', $result['viewtype']);
        self::assertSame('20260315', $result['Date']);
        self::assertSame('?prev', $result['PREV_DAY_URL']);
        self::assertSame('?next', $result['NEXT_DAY_URL']);
        self::assertArrayHasKey('timeRows', $result);
        self::assertArrayHasKey('providers', $result);
    }

    public function testBuildDayScreenShowFacilitySelectReflectsFacilityCount(): void
    {
        $builder = $this->builder(ViewType::Day);

        $oneFacility = $builder->buildDayScreenRenderData(
            ['2026-03-15' => []],
            [$this->makeProvider()],
            [$this->makeProvider()],
            [['id' => 1, 'name' => 'Main']],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            1,
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
            true
        );

        $twoFacilities = $builder->buildDayScreenRenderData(
            ['2026-03-15' => []],
            [$this->makeProvider()],
            [$this->makeProvider()],
            [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            1,
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
            true
        );

        self::assertFalse($oneFacility['showFacilitySelect']);
        self::assertTrue($twoFacilities['showFacilitySelect']);
    }

    public function testBuildWeekScreenRenderDataReturnsExpectedTopLevelKeys(): void
    {
        // See note on the month-screen test re: empty events.
        $builder = $this->builder(ViewType::Week);
        $events = [
            '2026-03-15' => [],
            '2026-03-16' => [],
        ];

        $result = $builder->buildWeekScreenRenderData(
            $events,
            [$this->makeProvider()],
            [$this->makeProvider()],
            [['id' => 1, 'name' => 'Main']],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            1,
            0,
            '/img',
            '/openemr',
            '?prev',
            '?next',
            'fa-chevron-left',
            'fa-chevron-right',
            '',
            true,
            'Mar 15 - Mar 21 2026',
            true
        );

        self::assertSame('week', $result['viewtype']);
        self::assertSame('20260315', $result['Date']);
        self::assertSame('?prev', $result['PREV_WEEK_URL']);
        self::assertSame('?next', $result['NEXT_WEEK_URL']);
        self::assertSame('Mar 15 - Mar 21 2026', $result['weekHeaderLabel']);

        $providers = $this->arrayAt($result, 'providers');
        self::assertCount(1, $providers);
        self::assertIsArray($providers[0]);
        $dayColumns = $providers[0]['dayColumns'];
        self::assertIsArray($dayColumns);
        self::assertCount(2, $dayColumns);
    }

    public function testEmptyProvidersListProducesEmptyProvidersGrid(): void
    {
        $builder = $this->builder(ViewType::DayPrint);

        $result = $builder->buildDayPrintRenderData(
            ['2026-03-15' => [$this->makeEvent()]],
            [],
            $this->makeTimes(),
            30,
            '20260315',
            $this->shortDayNames(),
            0,
            '/img',
            true
        );

        self::assertSame([], $result['providers']);
    }
}
