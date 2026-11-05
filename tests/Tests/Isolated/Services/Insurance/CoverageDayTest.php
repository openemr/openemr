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
    private static function days(): CoverageDay
    {
        // 23:30 on the 4th in Chicago is already the 5th in UTC.
        return new CoverageDay(new FrozenClock(new DateTimeImmutable('2026-11-04 23:30:00', new DateTimeZone('America/Chicago'))));
    }

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

    #[Test]
    #[DataProvider('stored')]
    public function aStoredValueIsItsCalendarDayOrNothing(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, CoverageDay::fromStored($value)?->format('Y-m-d'));
    }

    #[Test]
    public function todayIsTheClocksCalendarDayInItsOwnZone(): void
    {
        $today = self::days()->today();

        self::assertSame('2026-11-04 00:00:00', $today->format('Y-m-d H:i:s'));
        self::assertSame('America/Chicago', $today->getTimezone()->getName());
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

    #[Test]
    #[DataProvider('unreadable')]
    public function aVisitWithADayItCantReadAsksAboutNoDay(mixed $encounterDate): void
    {
        self::assertNull(self::days()->forVisit($encounterDate), 'the caller decides; today would bill the plan in force now');
    }

    #[Test]
    public function aVisitAsksAboutItsDate(): void
    {
        self::assertSame('2026-06-30', self::days()->forVisit('2026-06-30 16:45:00')?->format('Y-m-d'));
    }

    #[Test]
    public function aLabOrderAsksAboutTheCollectionDateThenTheOrderDate(): void
    {
        $days = self::days();

        self::assertSame('2026-10-02', $days->forLabOrder('2026-10-02 08:15:00', '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-01', $days->forLabOrder(null, '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-01', $days->forLabOrder('0000-00-00 00:00:00', '2026-10-01')?->format('Y-m-d'));
        self::assertSame('2026-10-01', $days->forLabOrder(false, '2026-10-01')?->format('Y-m-d'));
    }

    #[Test]
    #[DataProvider('unreadable')]
    public function aLabOrderWithNeitherDateReadableAsksAboutNoDay(mixed $date): void
    {
        self::assertNull(self::days()->forLabOrder($date, $date));
        self::assertNull(self::days()->forLabOrder($date, '06/30/2026'), 'an order date in the display format is not read either');
    }
}
