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
}
