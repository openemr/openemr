<?php

/**
 * DateInput (one conversion that refuses what it can't parse), DateRange (one half-open range form),
 * Period, PartialDate and SiteClock.
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
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Dates\DateInput;
use OpenEMR\Common\Dates\DateRange;
use OpenEMR\Common\Dates\Day;
use OpenEMR\Common\Dates\PartialDate;
use OpenEMR\Common\Dates\Period;
use OpenEMR\Common\Dates\Precision;
use OpenEMR\Common\Dates\SiteClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DateInputAndRangeTest extends TestCase
{
    /**
     * @return array<string, array{string, int, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function typed(): array
    {
        return [
            'ISO on an ISO site' => ['2026-11-04', DateInput::FORMAT_ISO, '2026-11-04'],
            'the checkbox\'s unpadded ISO, ISO site' => ['2026-11-4', DateInput::FORMAT_ISO, '2026-11-04'],
            'the checkbox\'s ISO on a US site' => ['2026-11-4', DateInput::FORMAT_US, '2026-11-04'],
            'the outcome\'s ISO on an international site' => ['2026-11-04', DateInput::FORMAT_INTL, '2026-11-04'],
            'US' => ['11/04/2026', DateInput::FORMAT_US, '2026-11-04'],
            'international' => ['04/11/2026', DateInput::FORMAT_INTL, '2026-11-04'],
            'unpadded US' => ['1/4/2026', DateInput::FORMAT_US, '2026-01-04'],
            'slashes on an ISO site' => ['11/04/2026', DateInput::FORMAT_ISO, null],
            'not a day' => ['02/30/2026', DateInput::FORMAT_US, null],
            'blank' => ['  ', DateInput::FORMAT_US, null],
            'a word' => ['today', DateInput::FORMAT_INTL, null],
            'a datetime is not a date' => ['2026-11-04 10:00', DateInput::FORMAT_US, null],
        ];
    }

    #[Test]
    #[DataProvider('typed')]
    public function aTypedDateIsTheDisplayFormatOrIsoOrNothing(string $typed, int $format, ?string $expected): void
    {
        self::assertSame($expected, DateInput::toIso($typed, $format));
    }

    #[Test]
    public function aRangeOfDaysKeepsTheLastDayWhole(): void
    {
        $range = DateRange::days('fe.date', Day::of(2026, 11, 1), Day::of(2026, 11, 4));
        self::assertSame('fe.date >= ? AND fe.date < ?', $range->sql);
        self::assertSame(['2026-11-01 00:00:00', '2026-11-05 00:00:00'], $range->binds);
    }

    #[Test]
    public function throughADayIsBeforeTheNextMidnight(): void
    {
        $end = DateRange::through('l.date', '2026-11-4');
        self::assertSame('l.date < ?', $end->sql);
        self::assertSame(['2026-11-05 00:00:00'], $end->binds);
        $end = DateRange::through('l.date', '2026-12-31');
        self::assertSame(['2027-01-01 00:00:00'], $end->binds);
    }

    #[Test]
    public function aBoundThatIsNotADayKeepsItsComparison(): void
    {
        foreach (['', '2026-11-04 23:59:59', '-11-04-2026-', '2026-02-30'] as $bound) {
            $end = DateRange::through('l.date', $bound);
            self::assertSame('l.date <= ?', $end->sql, $bound);
            self::assertSame([$bound], $end->binds, $bound);
        }
        $null = DateRange::through('l.date', null);
        self::assertSame('l.date <= ?', $null->sql);
        self::assertSame([null], $null->binds);
        self::assertSame(['9999-12-31'], DateRange::through('l.date', '9999-12-31')->binds);
        $both = DateRange::between('e.date', '2026-01-01', '2026-11-04');
        self::assertSame('e.date >= ? AND e.date < ?', $both->sql);
        self::assertSame(['2026-01-01', '2026-11-05 00:00:00'], $both->binds);
    }

    #[Test]
    public function aRangeOfDaysIsBuiltFromTheDaysNotAZone(): void
    {
        // America/Santiago skips 00:00 on 6 September 2026: the bound is still that day's midnight as stored
        $zone = date_default_timezone_get();
        date_default_timezone_set('America/Santiago');
        try {
            self::assertSame(['2026-09-06 00:00:00', '2026-09-07 00:00:00'], DateRange::days('c', Day::of(2026, 9, 6), Day::of(2026, 9, 6))->binds);
        } finally {
            date_default_timezone_set($zone);
        }
    }

    #[Test]
    public function aPeriodIsHalfOpen(): void
    {
        $zone = new DateTimeZone('America/Chicago');
        $days = Period::days(Day::of(2026, 11, 4), Day::of(2026, 11, 4), $zone);
        self::assertTrue($days->contains(new DateTimeImmutable('2026-11-04 23:59:59', $zone)));
        self::assertFalse($days->contains(new DateTimeImmutable('2026-11-05 00:00:00', $zone)));
        self::assertTrue($days->contains(new DateTimeImmutable('2026-11-04 00:00:00', $zone)));
        self::assertSame(['2026-11-04 00:00:00', '2026-11-05 00:00:00'], $days->storedBounds());
        $open = Period::between(null, null);
        self::assertTrue($open->contains(new DateTimeImmutable('1900-01-01')));
    }

    /**
     * @return array<string, array{string, ?string, ?Precision, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function hl7(): array
    {
        return [
            'a year' => ['2020', '2020-01-01 00:00:00', Precision::Year, '2021-01-01 00:00:00'],
            'a month' => ['202005', '2020-05-01 00:00:00', Precision::Month, '2020-06-01 00:00:00'],
            'a day' => ['20200515', '2020-05-15 00:00:00', Precision::Day, '2020-05-16 00:00:00'],
            'a minute with an offset' => ['202005151030-0500', '2020-05-15 10:30:00', Precision::Minute, '2020-05-15 10:31:00'],
            'a second with fractions' => ['20200515103000.123', '2020-05-15 10:30:00', Precision::Second, '2020-05-15 10:30:01'],
            'not a month' => ['202013', null, null, null],
            'an odd length' => ['20205', null, null, null],
            'not a TS' => ['2020-05-15', null, null, null],
            'blank' => ['', null, null, null],
        ];
    }

    #[Test]
    #[DataProvider('hl7')]
    public function aPartialDateKeepsItsPrecisionAndInventsNoDay(string $ts, ?string $first, ?Precision $precision, ?string $until): void
    {
        $zone = new DateTimeZone('America/Chicago');
        $value = PartialDate::fromHl7($ts);
        self::assertSame($first, $value?->firstInstant($zone)->format('Y-m-d H:i:s'));
        self::assertSame($precision, $value?->precision);
        self::assertSame($until, $value?->period($zone)->until?->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function theSiteClockReadsNowAndTodayInTheSitesZone(): void
    {
        $zone = date_default_timezone_get();
        date_default_timezone_set('America/Chicago');
        try {
            $clock = new SiteClock(new FrozenClock(new DateTimeImmutable('2026-11-05 04:30:00', new DateTimeZone('UTC'))));
            self::assertSame('2026-11-04 22:30:00', $clock->now()->format('Y-m-d H:i:s'));
            self::assertSame('2026-11-04', $clock->today()->iso());
            self::assertSame('America/Chicago', SiteClock::zone()->getName());
        } finally {
            date_default_timezone_set($zone);
        }
    }
}
