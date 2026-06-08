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

use OpenEMR\PostCalendar\ViewModel\CalendarViewModel;
use OpenEMR\PostCalendar\ViewModel\ViewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarViewModelTest extends TestCase
{
    public function testViewTypeIsExposedAsReadonlyProperty(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);
        self::assertSame(ViewType::Day, $vm->viewType);
    }

    /**
     * @param  list<int> $expected
     */
    #[DataProvider('dayOfWeekListProvider')]
    public function testDayOfWeekListStartsAtFirstDayAndWrapsAtSix(int $firstDayOfWeek, array $expected): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: $firstDayOfWeek);
        self::assertSame($expected, $vm->dayOfWeekList());
    }

    /**
     * @return iterable<string, array{int, list<int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dayOfWeekListProvider(): iterable
    {
        yield 'Sunday-first'    => [0, [0, 1, 2, 3, 4, 5, 6]];
        yield 'Monday-first'    => [1, [1, 2, 3, 4, 5, 6, 0]];
        yield 'Wednesday-first' => [3, [3, 4, 5, 6, 0, 1, 2]];
        yield 'Saturday-first'  => [6, [6, 0, 1, 2, 3, 4, 5]];
    }

    /**
     * @param  list<int> $expected
     */
    #[DataProvider('outOfRangeFirstDayProvider')]
    public function testOutOfRangeFirstDayOfWeekClampsToSunday(int $firstDayOfWeek, array $expected): void
    {
        // Legacy [-php-] block self-healed an out-of-range value with
        // pnModSetVar(..., 'pcFirstDayOfWeek', '0'). The view-model is
        // read-only — it returns the corrected list without persisting.
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: $firstDayOfWeek);
        self::assertSame($expected, $vm->dayOfWeekList());
    }

    /**
     * @return iterable<string, array{int, list<int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outOfRangeFirstDayProvider(): iterable
    {
        yield 'negative'      => [-1, [0, 1, 2, 3, 4, 5, 6]];
        yield 'beyond Saturday' => [7,  [0, 1, 2, 3, 4, 5, 6]];
        yield 'far out'       => [42, [0, 1, 2, 3, 4, 5, 6]];
    }

    public function testViewTypeIsPrintViewDistinguishesScreenAndPrintVariants(): void
    {
        self::assertFalse(ViewType::Day->isPrintView());
        self::assertFalse(ViewType::Week->isPrintView());
        self::assertFalse(ViewType::Month->isPrintView());
        self::assertTrue(ViewType::DayPrint->isPrintView());
        self::assertTrue(ViewType::WeekPrint->isPrintView());
        self::assertTrue(ViewType::MonthPrint->isPrintView());
    }

    public function testClinicHoursFilterAppliesToDayAndWeekOnly(): void
    {
        self::assertTrue(ViewType::Day->usesClinicHoursFilter());
        self::assertTrue(ViewType::Week->usesClinicHoursFilter());
        self::assertFalse(ViewType::Month->usesClinicHoursFilter());
        self::assertFalse(ViewType::DayPrint->usesClinicHoursFilter());
        self::assertFalse(ViewType::WeekPrint->usesClinicHoursFilter());
        self::assertFalse(ViewType::MonthPrint->usesClinicHoursFilter());
    }

    /**
     * @param  int    $catid
     * @param  string $expectedDay
     * @param  string $expectedWeek
     * @param  string $expectedMonth
     */
    #[DataProvider('eventClassPerViewProvider')]
    public function testEventClassForCategoryDispatchesByCategoryAndView(
        int $catid,
        string $expectedDay,
        string $expectedWeek,
        string $expectedMonth
    ): void {
        $day = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);
        $week = new CalendarViewModel(viewType: ViewType::Week, firstDayOfWeek: 0);
        $month = new CalendarViewModel(viewType: ViewType::Month, firstDayOfWeek: 0);

        self::assertSame($expectedDay, $day->eventClassForCategory($catid));
        self::assertSame($expectedWeek, $week->eventClassForCategory($catid));
        self::assertSame($expectedMonth, $month->eventClassForCategory($catid));
    }

    /**
     * @return iterable<string, array{int, string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function eventClassPerViewProvider(): iterable
    {
        // [catid, day-class, week-class, month-class]
        yield 'catid 0 (default)'  => [0,  'event_appointment', 'event_appointment', 'event_appointment'];
        yield 'catid 1 NO-SHOW'    => [1,  'event_noshow',      'event_noshow',      'event_noshow'];
        yield 'catid 2 IN'         => [2,  'event_in',          'event_in',          'event_in'];
        yield 'catid 3 OUT'        => [3,  'event_out',         'event_out',         'event_out'];
        yield 'catid 4 VACATION'   => [4,  'event_reserved',    'event_reserved',    'event_reserved'];
        yield 'catid 6 HOLIDAY'    => [6,  'event_holiday',     'event_holiday',     'event_holiday hiddenevent'];
        yield 'catid 8 LUNCH'      => [8,  'event_reserved',    'event_reserved',    'event_reserved'];
        yield 'catid 11 RESERVED'  => [11, 'event_reserved',    'event_reserved',    'event_reserved'];
        yield 'catid 99 week-only' => [99, 'event_appointment', 'event_holiday',     'event_appointment'];
        yield 'catid 42 unknown'   => [42, 'event_appointment', 'event_appointment', 'event_appointment'];
    }

    public function testTranslatedCategoryNameOverridesSpecialCategories(): void
    {
        $GLOBALS['disable_translation'] = true;
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        self::assertSame('IN',       $vm->translatedCategoryName(2,  'Whatever'));
        self::assertSame('OUT',      $vm->translatedCategoryName(3,  'Whatever'));
        self::assertSame('VACATION', $vm->translatedCategoryName(4,  'Whatever'));
        self::assertSame('LUNCH',    $vm->translatedCategoryName(8,  'Whatever'));
        self::assertSame('RESERVED', $vm->translatedCategoryName(11, 'Whatever'));
    }

    public function testTranslatedCategoryNamePassesThroughForOrdinaryCategories(): void
    {
        $GLOBALS['disable_translation'] = true;
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        self::assertSame('Office Visit',  $vm->translatedCategoryName(5,  'Office Visit'));
        self::assertSame('Follow-up',     $vm->translatedCategoryName(7,  'Follow-up'));
        self::assertSame('Anything',      $vm->translatedCategoryName(99, 'Anything'));
    }

    /**
     * @param  ?string $input
     * @param  array{lname: string, fname: string} $expected
     */
    #[DataProvider('patientNameProvider')]
    public function testParsePatientNameHandlesAllShapesLikeLegacy(?string $input, array $expected): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);
        self::assertSame($expected, $vm->parsePatientName($input));
    }

    /**
     * @return iterable<string, array{?string, array{lname: string, fname: string}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patientNameProvider(): iterable
    {
        yield 'lname, fname'         => ['Doe, Jane',    ['lname' => 'Doe',   'fname' => 'Jane']];
        yield 'no space after comma' => ['Doe,Jane',     ['lname' => 'Doe',   'fname' => 'Jane']];
        yield 'extra space'          => ['Doe,   Jane',  ['lname' => 'Doe',   'fname' => 'Jane']];
        yield 'no comma'             => ['Doe',          ['lname' => 'Doe',   'fname' => '']];
        yield 'empty string'         => ['',             ['lname' => '',      'fname' => '']];
        yield 'null'                 => [null,           ['lname' => '',      'fname' => '']];
        yield 'three commas'         => ['a, b, c',      ['lname' => 'a',     'fname' => 'b, c']];
    }

    public function testBuildMiniCalendarShapesForMarch2026SundayFirst(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Month, firstDayOfWeek: 0);

        $cal = $vm->buildMiniCalendar('2026-03-15', '20260315');

        self::assertSame(2026, $cal['year']);
        self::assertSame(3, $cal['month']);
        self::assertSame('March 2026', $cal['monthLabel']);

        // March 2026: starts Sunday March 1, ends Tuesday March 31.
        // With Sunday-first dowList, the grid pads to start Sun Mar 1 and
        // end Sat Apr 4 — that's 5 full weeks.
        self::assertCount(5, $cal['weeks']);
        foreach ($cal['weeks'] as $week) {
            self::assertCount(7, $week);
        }

        // First cell is Sunday Mar 1.
        self::assertSame('20260301', $cal['weeks'][0][0]['dateYmd']);
        self::assertSame(1,           $cal['weeks'][0][0]['day']);
        self::assertSame(0,           $cal['weeks'][0][0]['dayOfWeek']);
        self::assertTrue($cal['weeks'][0][0]['inMonth']);
        self::assertTrue($cal['weeks'][0][0]['isWeekend']);
        self::assertFalse($cal['weeks'][0][0]['isCurrent']);

        // Mar 15 is week 3, Sunday position. Marked current.
        self::assertSame('20260315', $cal['weeks'][2][0]['dateYmd']);
        self::assertTrue($cal['weeks'][2][0]['isCurrent']);
    }

    public function testBuildMiniCalendarMondayFirstAdjustsLeadingPadding(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Month, firstDayOfWeek: 1);

        $cal = $vm->buildMiniCalendar('2026-03-15');

        // Monday-first: first row starts on Monday. March 1 is Sunday, so
        // the first row's first cell is the previous Monday — Feb 23, 2026.
        self::assertSame('20260223', $cal['weeks'][0][0]['dateYmd']);
        self::assertFalse($cal['weeks'][0][0]['inMonth']);
        self::assertSame(1, $cal['weeks'][0][0]['dayOfWeek']);

        // Last cell of first row is the following Sunday — Mar 1.
        self::assertSame('20260301', $cal['weeks'][0][6]['dateYmd']);
        self::assertTrue($cal['weeks'][0][6]['inMonth']);
        self::assertSame(0, $cal['weeks'][0][6]['dayOfWeek']);
        self::assertTrue($cal['weeks'][0][6]['isWeekend']);
    }

    public function testBuildMiniCalendarMarksOutOfMonthDaysCorrectly(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Month, firstDayOfWeek: 0);

        $cal = $vm->buildMiniCalendar('2026-03-15');

        // Find any day with inMonth=false and confirm its month differs.
        $foundOutOfMonth = false;
        foreach ($cal['weeks'] as $week) {
            foreach ($week as $day) {
                if (!$day['inMonth']) {
                    $foundOutOfMonth = true;
                    $actualMonth = (int) substr($day['dateYmd'], 4, 2);
                    self::assertNotSame(3, $actualMonth);
                }
            }
        }
        self::assertTrue($foundOutOfMonth, 'Expected at least one out-of-month padding day');
    }

    public function testBuildMiniCalendarThrowsOnUnparseableAnchor(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Month, firstDayOfWeek: 0);

        $this->expectException(\InvalidArgumentException::class);
        $vm->buildMiniCalendar('not-a-date');
    }

    public function testNormalizeAllDayEventOverridesTimeAndDuration(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $event = [
            'eid'         => 42,
            'alldayevent' => 1,
            'startTime'   => '14:30:00',  // ignored when alldayevent=1
            'duration'    => 1800,          // ignored when alldayevent=1
        ];

        // Clinic is 9:00 (540 min) to 17:00 (1020 min). First slot at 9:00.
        $normalized = $vm->normalizeAllDayEvent($event, ['hour' => 9, 'minute' => 0], 540, 1020);

        self::assertSame('09:00:00', $normalized['startTime']);
        self::assertSame((1020 - 540) * 60, $normalized['duration']);  // 28800 sec = 8h
        self::assertSame(42, $normalized['eid'], 'unrelated keys should pass through');
    }

    public function testNormalizeAllDayEventPadsSingleDigitHourAndMinute(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $event = ['alldayevent' => 1, 'startTime' => '00:00:00', 'duration' => 0];
        $normalized = $vm->normalizeAllDayEvent($event, ['hour' => '8', 'minute' => '5'], 480, 1020);

        self::assertSame('08:05:00', $normalized['startTime']);
    }

    public function testNormalizeAllDayEventLeavesNonAllDayUntouched(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $event = [
            'alldayevent' => 0,
            'startTime'   => '10:15:00',
            'duration'    => 1800,
        ];

        $result = $vm->normalizeAllDayEvent($event, ['hour' => 9, 'minute' => 0], 540, 1020);

        self::assertSame('10:15:00', $result['startTime']);
        self::assertSame(1800, $result['duration']);
    }

    public function testStartTimeToMinutesParsesHHMMAndHHMMSS(): void
    {
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        self::assertSame(0,    $vm->startTimeToMinutes('00:00:00'));
        self::assertSame(0,    $vm->startTimeToMinutes('00:00'));
        self::assertSame(540,  $vm->startTimeToMinutes('09:00:00'));
        self::assertSame(615,  $vm->startTimeToMinutes('10:15:00'));
        self::assertSame(1439, $vm->startTimeToMinutes('23:59:00'));
    }

    public function testStartTimeToMinutesTreatsMalformedInputAsZero(): void
    {
        // Matches legacy `(int) substr(...)` coercion — falls back to 0
        // rather than throwing.
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);
        self::assertSame(0, $vm->startTimeToMinutes(''));
        self::assertSame(0, $vm->startTimeToMinutes('no-colon'));
    }

    public function testComputeEventGeometryForAlignedSingleSlotEvent(): void
    {
        // Clinic starts at minute 540 (9am). Event starts at minute 540,
        // duration 30 min, slot interval 30 min, slot height 20px.
        // Expected: startInterval=0, endInterval=1, top=0px, height=20px.
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $geo = $vm->computeEventGeometry(
            startMinFromMidnight: 540,
            durationMinutes: 30,
            clinicStartMin: 540,
            intervalMinutes: 30,
            timeslotHeightVal: 20,
            timeslotHeightUnit: 'px',
        );

        self::assertSame(0, $geo['startInterval']);
        self::assertSame(1, $geo['endInterval']);
        self::assertSame('0px',  $geo['top']);
        self::assertSame('20px', $geo['height']);
    }

    public function testComputeEventGeometryForMultiSlotEvent(): void
    {
        // 30-min slots, 20px tall. Event at 10:00 for 90 min → starts at
        // slot index 2 (60 min after 9am), spans 3 slots.
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $geo = $vm->computeEventGeometry(600, 90, 540, 30, 20, 'px');

        self::assertSame(2, $geo['startInterval']);
        self::assertSame(5, $geo['endInterval']);
        self::assertSame('40px', $geo['top']);
        self::assertSame('60px', $geo['height']);
    }

    public function testComputeEventGeometryRoundsStartDownAndEndUp(): void
    {
        // 30-min slots. Event at 9:15 for 30 min should snap to start at
        // slot 0 (floor of 0.5) and end at slot 2 (ceil of 1.5) — height
        // grows when start straddles a boundary.
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $geo = $vm->computeEventGeometry(555, 30, 540, 30, 20, 'px');

        self::assertSame(0, $geo['startInterval']);
        self::assertSame(2, $geo['endInterval']);
        self::assertSame('0px',  $geo['top']);
        self::assertSame('40px', $geo['height']);
    }

    public function testComputeEventGeometryPropagatesUnitString(): void
    {
        // The legacy code concatenated whatever unit string the caller passed
        // (default "px", but `em` / `%` are equally valid in CSS).
        $vm = new CalendarViewModel(viewType: ViewType::Day, firstDayOfWeek: 0);

        $geo = $vm->computeEventGeometry(540, 60, 540, 30, 1, 'em');
        self::assertSame('0em', $geo['top']);
        self::assertSame('2em', $geo['height']);
    }
}
