<?php

/**
 * Day: the shared calendar-day type, and its agreement with insurance's coverage day.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Dates;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use OpenEMR\Common\Dates\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DayTest extends TestCase
{
    /**
     * The coverage day's own cases (#15038, CoverageDayTest::stored), so Day::fromStored() answers as it does
     * and CoverageDay::fromStored() can delegate to it.
     *
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

    #[Test]
    #[DataProvider('stored')]
    public function aStoredValueIsItsCalendarDayOrNothing(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, Day::fromStored($value)?->iso());
    }

    #[Test]
    public function isoIsExactlyYearMonthDay(): void
    {
        self::assertSame('2024-02-29', Day::fromIso('2024-02-29')?->iso());
        self::assertNull(Day::fromIso('2025-02-29'));
        self::assertNull(Day::fromIso('2026-11-4'));
        self::assertNull(Day::fromIso('2026-11-04 10:00:00'));
        self::assertNull(Day::fromIso('0000-01-01'));
    }

    #[Test]
    public function ofRefusesWhatIsNotADay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Day::of(2026, 2, 30);
    }

    #[Test]
    public function anInstantsDayIsTheDayInItsOwnZone(): void
    {
        // 23:30 on the 4th in Chicago is the 5th in UTC
        $late = new DateTimeImmutable('2026-11-04 23:30:00', new DateTimeZone('America/Chicago'));
        self::assertSame('2026-11-04', Day::fromDateTime($late)->iso());
        self::assertSame('2026-11-05', Day::fromDateTime($late->setTimezone(new DateTimeZone('UTC')))->iso());
    }

    #[Test]
    public function daysMoveAcrossMonthsYearsAndDaylightSaving(): void
    {
        self::assertSame('2027-01-01', Day::of(2026, 12, 31)->plusDays(1)->iso());
        self::assertSame('2024-02-29', Day::of(2024, 3, 1)->plusDays(-1)->iso());
        // the US autumn change (1 November 2026) is a 25-hour day: still one day on
        self::assertSame('2026-11-02', Day::of(2026, 11, 1)->plusDays(1)->iso());
        self::assertSame(
            '2026-11-02 00:00:00',
            Day::of(2026, 11, 1)->plusDays(1)->firstInstantIn(new DateTimeZone('America/Chicago'))->format('Y-m-d H:i:s')
        );
    }

    #[Test]
    public function daysCompareAsTheCalendarOrdersThem(): void
    {
        $a = Day::of(2026, 1, 31);
        $b = Day::of(2026, 2, 1);
        self::assertTrue($a->isBefore($b));
        self::assertTrue($b->isAfter($a));
        self::assertTrue($a->equals(Day::of(2026, 1, 31)));
        self::assertSame('2026-01-31 00:00:00', $a->storedStart());
        self::assertSame('2026-01-31', (string)$a);
    }
}
