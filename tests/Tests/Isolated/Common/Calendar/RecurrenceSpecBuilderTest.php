<?php

/**
 * Isolated tests for RecurrenceSpecBuilder.
 *
 * Exercises the REPEAT vs REPEAT_ON branching that add_edit_event.php depends
 * on when saving recurring calendar events. Covers the openemr/openemr#11407
 * regression: freq type 5 must route to REPEAT_ON (pc_recurrtype = 2), not
 * REPEAT, or the __increment() fast-forward loop spins forever.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\RecurrenceSpecBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RecurrenceSpecBuilderTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        // Pin the timezone so strtotime()/date() interpret date-only strings
        // identically across environments.
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    #[DataProvider('repeatTypesBelowThresholdProvider')]
    public function testTypesZeroThroughFourAreRepeat(int $repeatType): void
    {
        $spec = RecurrenceSpecBuilder::fromRepeatForm('2024-03-21', $repeatType, 3);

        $this->assertSame(1, $spec->recurrType, 'Types <= 4 must remain REPEAT');
        $this->assertSame($repeatType, $spec->repeatType, 'REPEAT preserves freq type');
        $this->assertSame(3, $spec->repeatFreq, 'REPEAT preserves freq interval');
        $this->assertSame(0, $spec->repeatOnDay);
        $this->assertSame(1, $spec->repeatOnNum);
        $this->assertSame(0, $spec->repeatOnFreq);
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function repeatTypesBelowThresholdProvider(): array
    {
        return [
            'type 0 (none)'    => [0],
            'type 1 (daily)'   => [1],
            'type 2 (weekly)'  => [2],
            'type 3 (monthly)' => [3],
            'type 4 (yearly)'  => [4],
        ];
    }

    /**
     * Regression test for openemr/openemr#11407.
     *
     * 2024-03-21 is the 3rd Thursday of March 2024. Pre-fix, type 5 hit the
     * `> 6` threshold and fell through to REPEAT with an unhandled freq type,
     * causing the postcalendar iterator to loop forever. After the fix, type
     * 5 routes to REPEAT_ON with repeat_on_num = 3 (3rd occurrence).
     */
    public function testTypeFiveRoutesToRepeatOnForNthWeekdayIssue11407(): void
    {
        $spec = RecurrenceSpecBuilder::fromRepeatForm('2024-03-21', 5, 1);

        $this->assertSame(2, $spec->recurrType, 'Type 5 must route to REPEAT_ON');
        $this->assertSame(0, $spec->repeatType, 'REPEAT_ON zeroes freq type');
        $this->assertSame(0, $spec->repeatFreq, 'REPEAT_ON zeroes freq interval');
        $this->assertSame(4, $spec->repeatOnDay, 'Thursday = 4');
        $this->assertSame(3, $spec->repeatOnNum, '3rd Thursday of March 2024');
        $this->assertSame(1, $spec->repeatOnFreq, 'Original freq interval preserved');
    }

    public function testTypeSixEncodesLastWeekdayAsNumFive(): void
    {
        // 2024-03-21 is a Thursday, the 3rd of the month. Regardless of which
        // Thursday it is, type 6 always encodes as "last" (num = 5).
        $spec = RecurrenceSpecBuilder::fromRepeatForm('2024-03-21', 6, 2);

        $this->assertSame(2, $spec->recurrType);
        $this->assertSame(4, $spec->repeatOnDay, 'Thursday = 4');
        $this->assertSame(5, $spec->repeatOnNum, 'Type 6 = last weekday of month');
        $this->assertSame(2, $spec->repeatOnFreq);
    }

    #[DataProvider('nthWeekdayProvider')]
    public function testNthWeekdayComputation(
        string $date,
        int $repeatType,
        int $expectedDay,
        int $expectedNum,
    ): void {
        $spec = RecurrenceSpecBuilder::fromRepeatForm($date, $repeatType, 1);

        $this->assertSame(2, $spec->recurrType);
        $this->assertSame($expectedDay, $spec->repeatOnDay);
        $this->assertSame($expectedNum, $spec->repeatOnNum);
    }

    /**
     * Covers the `intdiv((day - 1) / 7) + 1` computation across:
     *   - Each nth-occurrence repeat type (5, 7, 8, 9) which all share the
     *     same formula.
     *   - Every possible nth value (1st through 5th) in a single month.
     *   - Different weekdays, to prove the day-of-week is read from the date.
     *
     * @return array<string, array{string, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nthWeekdayProvider(): array
    {
        return [
            // March 2024 Thursdays: 7, 14, 21, 28 (no 5th Thursday).
            '1st Thursday (type 5)' => ['2024-03-07', 5, 4, 1],
            '2nd Thursday (type 5)' => ['2024-03-14', 5, 4, 2],
            '3rd Thursday (type 5)' => ['2024-03-21', 5, 4, 3],
            '4th Thursday (type 5)' => ['2024-03-28', 5, 4, 4],

            // March 2024 Sundays: 3, 10, 17, 24, 31 (5 Sundays).
            '1st Sunday (type 5)' => ['2024-03-03', 5, 0, 1],
            '5th Sunday (type 5)' => ['2024-03-31', 5, 0, 5],

            // Boundary days 1, 7, 8, 14, 15 to exercise the intdiv.
            'day 1 -> nth 1'  => ['2024-03-01', 5, 5, 1],
            'day 7 -> nth 1'  => ['2024-03-07', 5, 4, 1],
            'day 8 -> nth 2'  => ['2024-03-08', 5, 5, 2],
            'day 14 -> nth 2' => ['2024-03-14', 5, 4, 2],
            'day 15 -> nth 3' => ['2024-03-15', 5, 5, 3],

            // Types 7, 8, 9 share the formula with type 5.
            'type 7 nth weekday' => ['2024-03-21', 7, 4, 3],
            'type 8 nth weekday' => ['2024-03-21', 8, 4, 3],
            'type 9 nth weekday' => ['2024-03-21', 9, 4, 3],
        ];
    }

    public function testUnparsableDateFallsBackToRepeat(): void
    {
        // Defence in depth: the caller validates dates, but if something
        // unparsable slips through we fall back to REPEAT rather than
        // persisting a REPEAT_ON row with an unknown weekday.
        $spec = RecurrenceSpecBuilder::fromRepeatForm('not a date', 5, 2);

        $this->assertSame(1, $spec->recurrType);
        $this->assertSame(5, $spec->repeatType);
        $this->assertSame(2, $spec->repeatFreq);
        $this->assertSame(0, $spec->repeatOnDay);
        $this->assertSame(1, $spec->repeatOnNum);
        $this->assertSame(0, $spec->repeatOnFreq);
    }

}
