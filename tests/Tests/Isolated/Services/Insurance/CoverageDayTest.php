<?php

/**
 * CoverageDay: the day each use asks the coverage service about.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Services\Insurance\CoverageDay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CoverageDayTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stored(): array
    {
        return [
            'a date' => ['2026-11-04', '2026-11-04'],
            'a datetime: its day' => ['2026-11-04 23:30:00', '2026-11-04'],
            'null' => [null, null],
            'blank' => ['', null],
            'zero' => ['0000-00-00', null],
            'a zero datetime' => ['0000-00-00 00:00:00', null],
            'a zero day' => ['2026-05-00', null],
            'not a day' => ['2026-02-30', null],
            'too short' => ['2026-11', null],
            'not a date' => ['yesterday!', null],
            'not a string: a number' => [20261104, null],
            'not a string: false (a failed fetch)' => [false, null],
            'not a string: an array' => [['2026-11-04'], null],
        ];
    }

    /**
     * A DATE or DATETIME gives its calendar day; blank, zero, partial or impossible dates and non-strings give none.
     */
    #[Test]
    #[DataProvider('stored')]
    public function aStoredValueIsItsCalendarDayOrNothing(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, CoverageDay::fromStored($value)?->format('Y-m-d'));
    }

    /**
     * A DATETIME late in the day gives that day at midnight in PHP's default zone, where the rule's dates are.
     */
    #[Test]
    public function theDayIsMidnightInPhpsZoneAsTheRulesDatesAre(): void
    {
        $day = CoverageDay::fromStored('2026-11-04 23:30:00');

        self::assertNotNull($day);
        self::assertSame('2026-11-04 00:00:00', $day->format('Y-m-d H:i:s'));
        self::assertSame(date_default_timezone_get(), $day->getTimezone()->getName());
    }

    /**
     * Today is the clock's calendar day, at midnight in PHP's default zone as the rule's dates are, even where
     * the clock's zone is a day ahead.
     */
    #[Test]
    public function todayIsTheClocksDayAtMidnightInPhpsZone(): void
    {
        $clock = new FrozenClock(new DateTimeImmutable('2026-11-05 01:30:00', new DateTimeZone('Pacific/Kiritimati')));

        $today = CoverageDay::today($clock);

        self::assertSame('2026-11-05 00:00:00', $today->format('Y-m-d H:i:s'));
        self::assertSame(date_default_timezone_get(), $today->getTimezone()->getName());
    }

    /**
     * A lab order is billed on its collection day, else its order date, each read as fromStored() reads it.
     */
    #[Test]
    public function aLabOrderAsksAboutTheCollectionDateThenTheOrderDate(): void
    {
        self::assertSame('2026-10-02', CoverageDay::forLabOrder('2026-10-02 08:15:00', '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-02', CoverageDay::forLabOrder('2026-10-02 23:30:00', '2026-10-03 00:10:00')?->format('Y-m-d'));
        self::assertSame('2026-10-01', CoverageDay::forLabOrder(null, '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-01', CoverageDay::forLabOrder('', '2026-10-01 14:00:00')?->format('Y-m-d'));
        self::assertSame('2026-10-01', CoverageDay::forLabOrder('0000-00-00 00:00:00', '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-01', CoverageDay::forLabOrder(false, '2026-10-01')?->format('Y-m-d'));
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unreadable(): array
    {
        return [
            'null' => [null],
            'blank' => [''],
            'zero' => ['0000-00-00'],
            'a zero datetime' => ['0000-00-00 00:00:00'],
            'the display format' => ['05/01/2025'],
            'not a string: false (a failed fetch)' => [false],
        ];
    }

    /**
     * With neither date readable there is no day: the caller decides, where today would bill the plan in force now.
     */
    #[Test]
    #[DataProvider('unreadable')]
    public function aLabOrderWithNeitherDateReadableAsksAboutNoDay(mixed $date): void
    {
        self::assertNull(CoverageDay::forLabOrder($date, $date));
        self::assertNull(CoverageDay::forLabOrder($date, '06/30/2026'), 'an order date in the display format is not read either');
    }
}
