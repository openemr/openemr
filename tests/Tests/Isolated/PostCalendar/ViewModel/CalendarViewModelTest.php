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
}
