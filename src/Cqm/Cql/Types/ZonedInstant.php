<?php

/**
 * A moment on the proleptic Gregorian calendar at a fixed UTC offset, with
 * the calendar arithmetic of Luxon 3 (the date library cql-execution uses).
 *
 * CQL date arithmetic is defined in terms of calendar fields, and
 * cql-execution delegates it to Luxon: adding a month clamps to the last day
 * of the shorter month, fractional amounts follow Luxon's "casual" duration
 * conversions, and differences in calendar units use Luxon's diff algorithm.
 * PHP's DateTime does none of these the same way (adding a month to January
 * 31 overflows into March), so this class reimplements them with integer
 * arithmetic. Only fixed offsets exist here; CQL date/times carry an offset,
 * never a named zone, so daylight saving time never applies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class ZonedInstant
{
    private const MS_PER_SECOND = 1000;
    private const MS_PER_MINUTE = 60_000;
    private const MS_PER_HOUR = 3_600_000;
    private const MS_PER_DAY = 86_400_000;

    public int $year;
    public int $month;
    public int $day;
    public int $hour;
    public int $minute;
    public int $second;
    public int $millisecond;

    private function __construct(public int $epochMs, public int $offsetMinutes)
    {
        $local = $epochMs + $offsetMinutes * self::MS_PER_MINUTE;
        $days = intdiv($local, self::MS_PER_DAY);
        $msOfDay = $local - $days * self::MS_PER_DAY;
        if ($msOfDay < 0) {
            $days--;
            $msOfDay += self::MS_PER_DAY;
        }
        [$this->year, $this->month, $this->day] = self::civilFromDays($days);
        $this->hour = intdiv($msOfDay, self::MS_PER_HOUR);
        $this->minute = intdiv($msOfDay % self::MS_PER_HOUR, self::MS_PER_MINUTE);
        $this->second = intdiv($msOfDay % self::MS_PER_MINUTE, self::MS_PER_SECOND);
        $this->millisecond = $msOfDay % self::MS_PER_SECOND;
    }

    /**
     * The instant whose local calendar fields, at the given offset, are the
     * ones passed. Fields out of range roll over (month 13 is January of the
     * next year), as Date.UTC does in Luxon.
     */
    public static function fromLocal(
        int $year,
        int $month = 1,
        int $day = 1,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        int $millisecond = 0,
        int $offsetMinutes = 0,
    ): self {
        // Normalize the month first so the day count starts from a real month.
        $monthIndex = $month - 1;
        $year += intdiv($monthIndex, 12) - ($monthIndex % 12 < 0 ? 1 : 0);
        $month = (($monthIndex % 12) + 12) % 12 + 1;

        $local = (self::daysFromCivil($year, $month, 1) + $day - 1) * self::MS_PER_DAY
            + $hour * self::MS_PER_HOUR
            + $minute * self::MS_PER_MINUTE
            + $second * self::MS_PER_SECOND
            + $millisecond;
        return new self($local - $offsetMinutes * self::MS_PER_MINUTE, $offsetMinutes);
    }

    /** ISO weekday: 1 is Monday, 7 is Sunday. */
    public function weekday(): int
    {
        $days = self::daysFromCivil($this->year, $this->month, $this->day);
        // 1970-01-01 was a Thursday (4).
        return ((($days + 3) % 7) + 7) % 7 + 1;
    }

    /** The same instant shown at another offset (Luxon setZone). */
    public function withOffset(int $offsetMinutes): self
    {
        return new self($this->epochMs, $offsetMinutes);
    }

    /** The same local fields reinterpreted at UTC (Luxon toUTC(0, {keepLocalTime: true})). */
    public function toUtcKeepingLocalTime(): self
    {
        return self::fromLocal(
            $this->year,
            $this->month,
            $this->day,
            $this->hour,
            $this->minute,
            $this->second,
            $this->millisecond,
        );
    }

    /**
     * Luxon plus({ [unit]: amount }). Years, months, weeks and days move the
     * calendar fields by their whole part, clamping the day to the target
     * month; fractions of them, and every smaller unit, are added as elapsed
     * milliseconds using Luxon's casual conversions (a year is 365 days, a
     * month 30).
     */
    public function plus(Precision $unit, int|float $amount): self
    {
        $whole = (int) $amount;
        $fraction = $amount - $whole;

        [$years, $months, $days, $millis] = match ($unit) {
            Precision::Year => [$whole, 0, 0, $fraction * 365 * self::MS_PER_DAY],
            Precision::Month => [0, $whole, 0, $fraction * 30 * self::MS_PER_DAY],
            Precision::Week => [0, 0, $whole * 7, $fraction * 7 * self::MS_PER_DAY],
            Precision::Day => [0, 0, $whole, $fraction * self::MS_PER_DAY],
            Precision::Hour => [0, 0, 0, $amount * self::MS_PER_HOUR],
            Precision::Minute => [0, 0, 0, $amount * self::MS_PER_MINUTE],
            Precision::Second => [0, 0, 0, $amount * self::MS_PER_SECOND],
            Precision::Millisecond => [0, 0, 0, $amount],
        };

        $year = $this->year + $years;
        $month = $this->month + $months;
        $day = min($this->day, self::daysInMonth($year, $month)) + $days;
        $moved = self::fromLocal(
            $year,
            $month,
            $day,
            $this->hour,
            $this->minute,
            $this->second,
            $this->millisecond,
            $this->offsetMinutes,
        );
        if ($millis == 0) {
            return $moved;
        }
        // Luxon keeps the fractional timestamp; JavaScript truncates it toward zero.
        return new self((int) ($moved->epochMs + $millis), $this->offsetMinutes);
    }

    /** Luxon startOf(unit), with weeks starting on Monday. */
    public function startOf(Precision $unit): self
    {
        $start = match ($unit) {
            Precision::Year => [$this->year, 1, 1, 0, 0, 0, 0],
            Precision::Month => [$this->year, $this->month, 1, 0, 0, 0, 0],
            Precision::Week, Precision::Day => [$this->year, $this->month, $this->day, 0, 0, 0, 0],
            Precision::Hour => [$this->year, $this->month, $this->day, $this->hour, 0, 0, 0],
            Precision::Minute => [$this->year, $this->month, $this->day, $this->hour, $this->minute, 0, 0],
            Precision::Second => [$this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second, 0],
            Precision::Millisecond => [$this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second, $this->millisecond],
        };
        $instant = self::fromLocal(...[...$start, $this->offsetMinutes]);
        if ($unit === Precision::Week) {
            $instant = $instant->plus(Precision::Day, 1 - $this->weekday());
        }
        return $instant;
    }

    /** Luxon endOf(unit): the last millisecond of the unit. */
    public function endOf(Precision $unit): self
    {
        return $this->plus($unit, 1)->startOf($unit)->plus(Precision::Millisecond, -1);
    }

    /**
     * cql-execution's truncation before a difference: the start of the unit,
     * where a week starts on the Sunday on or before the date.
     */
    public function truncate(Precision $unit): self
    {
        if ($unit !== Precision::Week) {
            return $this->startOf($unit);
        }
        $weekday = $this->weekday();
        return $this->plus(Precision::Day, $weekday === 7 ? 0 : -$weekday)->startOf(Precision::Day);
    }

    /**
     * The whole number of units from $other to this instant, as
     * cql-execution computes it: Luxon's this.diff(other, unit), truncated
     * toward zero.
     */
    public function wholeUnitsSince(self $other, Precision $unit): int
    {
        $otherIsLater = $other->epochMs > $this->epochMs;
        $earlier = $otherIsLater ? $this : $other;
        $later = $otherIsLater ? $other : $this;
        $value = self::diff($earlier, $later, $unit);
        $signed = $otherIsLater ? -$value : $value;
        return (int) ($signed >= 0 ? floor($signed) : ceil($signed));
    }

    /** Luxon's diff of one unit between ordered instants. */
    private static function diff(self $earlier, self $later, Precision $unit): float
    {
        $lowerOrder = match ($unit) {
            Precision::Hour => self::MS_PER_HOUR,
            Precision::Minute => self::MS_PER_MINUTE,
            Precision::Second => self::MS_PER_SECOND,
            Precision::Millisecond => 1,
            Precision::Year, Precision::Month, Precision::Week, Precision::Day => null,
        };
        if ($lowerOrder !== null) {
            return ($later->epochMs - $earlier->epochMs) / $lowerOrder;
        }

        $count = match ($unit) {
            Precision::Year => $later->year - $earlier->year,
            Precision::Month => $later->month - $earlier->month + ($later->year - $earlier->year) * 12,
            Precision::Week => intdiv(self::dayDiff($earlier, $later), 7),
            Precision::Day => self::dayDiff($earlier, $later),
        };
        $highWater = $earlier->plus($unit, $count);
        if ($highWater->epochMs > $later->epochMs) {
            // Overshot: back up one unit, and once more if still past.
            $count--;
            $cursor = $earlier->plus($unit, $count);
            if ($cursor->epochMs > $later->epochMs) {
                $highWater = $cursor;
                $count--;
                $cursor = $earlier->plus($unit, $count);
            }
        } else {
            $cursor = $highWater;
        }

        $remaining = $later->epochMs - $cursor->epochMs;
        if ($highWater->epochMs < $later->epochMs) {
            $highWater = $cursor->plus($unit, 1);
        }
        $span = $highWater->epochMs - $cursor->epochMs;
        return $span !== 0 ? $count + $remaining / $span : (float) $count;
    }

    /** Whole calendar days between the local dates (Luxon's dayDiff). */
    private static function dayDiff(self $earlier, self $later): int
    {
        return self::daysFromCivil($later->year, $later->month, $later->day)
            - self::daysFromCivil($earlier->year, $earlier->month, $earlier->day);
    }

    public static function daysInMonth(int $year, int $month): int
    {
        $monthIndex = $month - 1;
        $year += intdiv($monthIndex, 12) - ($monthIndex % 12 < 0 ? 1 : 0);
        $month = (($monthIndex % 12) + 12) % 12 + 1;
        if ($month === 2) {
            $leap = ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;
            return $leap ? 29 : 28;
        }
        return in_array($month, [4, 6, 9, 11], true) ? 30 : 31;
    }

    /** Days since 1970-01-01 for a proleptic Gregorian date (H. Hinnant's algorithm). */
    private static function daysFromCivil(int $year, int $month, int $day): int
    {
        $year -= $month <= 2 ? 1 : 0;
        $era = intdiv($year >= 0 ? $year : $year - 399, 400);
        $yearOfEra = $year - $era * 400;
        $dayOfYear = intdiv(153 * ($month + ($month > 2 ? -3 : 9)) + 2, 5) + $day - 1;
        $dayOfEra = $yearOfEra * 365 + intdiv($yearOfEra, 4) - intdiv($yearOfEra, 100) + $dayOfYear;
        return $era * 146097 + $dayOfEra - 719468;
    }

    /**
     * @return array{int, int, int} year, month, day
     */
    private static function civilFromDays(int $days): array
    {
        $days += 719468;
        $era = intdiv($days >= 0 ? $days : $days - 146096, 146097);
        $dayOfEra = $days - $era * 146097;
        $yearOfEra = intdiv($dayOfEra - intdiv($dayOfEra, 1460) + intdiv($dayOfEra, 36524) - intdiv($dayOfEra, 146096), 365);
        $dayOfYear = $dayOfEra - (365 * $yearOfEra + intdiv($yearOfEra, 4) - intdiv($yearOfEra, 100));
        $monthIndex = intdiv(5 * $dayOfYear + 2, 153);
        $day = $dayOfYear - intdiv(153 * $monthIndex + 2, 5) + 1;
        $month = $monthIndex + ($monthIndex < 10 ? 3 : -9);
        return [$yearOfEra + $era * 400 + ($month <= 2 ? 1 : 0), $month, $day];
    }
}
