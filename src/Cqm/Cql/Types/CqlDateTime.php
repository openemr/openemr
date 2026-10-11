<?php

/**
 * The CQL DateTime type, ported from cql-execution 3.3.2.
 *
 * The timezone offset is in hours and may be fractional (5.5). Where
 * cql-execution falls back to the Node process's local timezone (a string
 * with no offset, a Date promoted to a DateTime), this uses UTC: patient data
 * and the measurement period are already UTC, the parity corpus was captured
 * in UTC, and the result no longer depends on the server's timezone setting.
 * A Time is a DateTime on 0000-01-01 with a null offset, as in
 * cql-execution.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final class CqlDateTime extends CqlTemporal implements \Stringable
{
    /** The offset used where cql-execution would use the machine's local timezone. */
    public const LOCAL_OFFSET = 0.0;

    private const PARSE_PATTERN = '/(\d{4})(-(\d{2}))?(-(\d{2}))?(T((\d{2})(:(\d{2})(:(\d{2})(\.(\d+))?)?)?)?(Z|(([+-])(\d{2})(:?(\d{2}))?))?)?/';

    /**
     * The string shapes Luxon accepts for a DateTime, as regular expressions
     * over the whole string ('Z' is UTC, ZZ an offset +hh:mm).
     */
    private const VALID_SHAPES = [
        '/^\d{4}$/',
        '/^\d{4}-\d{2}$/',
        '/^\d{4}-\d{2}-\d{2}$/',
        '/^\d{4}-\d{2}-\d{2}T(Z|[+-]\d{2}:\d{2})$/',
        '/^\d{4}-\d{2}-\d{2}T\d{2}(Z|[+-]\d{2}:\d{2})?$/',
        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})?$/',
        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})?$/',
        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}(Z|[+-]\d{2}:\d{2})?$/',
    ];

    private static ?self $maximum = null;
    private static ?self $minimum = null;

    public function __construct(
        public readonly ?int $year = null,
        public readonly ?int $month = null,
        public readonly ?int $day = null,
        public readonly ?int $hour = null,
        public readonly ?int $minute = null,
        public readonly ?int $second = null,
        public readonly ?int $millisecond = null,
        public readonly ?float $timezoneOffset = self::LOCAL_OFFSET,
    ) {
    }

    public static function maximum(): self
    {
        return self::$maximum ??= new self(9999, 12, 31, 23, 59, 59, 999);
    }

    public static function minimum(): self
    {
        return self::$minimum ??= new self(1, 1, 1, 0, 0, 0, 0);
    }

    /**
     * Parses a CQL/ISO DateTime string at any precision, or returns null
     * when it is not a valid one.
     */
    public static function parse(string $string): ?self
    {
        if (preg_match(self::PARSE_PATTERN, $string, $m) !== 1) {
            return null;
        }
        $part = static fn (int $i): ?string => isset($m[$i]) && $m[$i] !== '' ? $m[$i] : null;

        $milliseconds = $part(14);
        if ($milliseconds !== null) {
            // Pad or truncate to exactly three digits: .5 is 500, .54321 is 543.
            $normalized = substr($milliseconds . '00', 0, 3);
            $string = self::replaceMilliseconds($string, $normalized);
            $milliseconds = $normalized;
        }
        if (!self::isValidString($string)) {
            return null;
        }

        $int = static fn (?string $v): ?int => $v === null ? null : (int) $v;
        $offset = self::LOCAL_OFFSET;
        if ($part(18) !== null) {
            $hours = (int) $part(18) + ($part(20) !== null ? (int) $part(20) / 60 : 0);
            $offset = (float) ($part(17) === '+' ? $hours : -$hours);
        } elseif ($part(15) === 'Z') {
            $offset = 0.0;
        }
        return new self(
            $int($part(1)),
            $int($part(3)),
            $int($part(5)),
            $int($part(8)),
            $int($part(10)),
            $int($part(12)),
            $int($milliseconds),
            $offset,
        );
    }

    /**
     * The DateTime cqm-models makes from a patient data string: JavaScript
     * `new Date(string)` read back at offset 0, so always at millisecond
     * precision and in UTC. A date-only string is midnight UTC; a date-time
     * with no offset is local time (here UTC, see LOCAL_OFFSET).
     */
    public static function fromQdmString(string $string): ?self
    {
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d+))?)?(Z|[+-]\d{2}:\d{2})?)?$/';
        if (preg_match($pattern, $string, $m) !== 1) {
            return null;
        }
        // JavaScript reads a date-only string as UTC and a date-time without
        // an offset as local time.
        $hasTime = ($m[4] ?? '') !== '';
        $offsetMinutes = $hasTime ? self::offsetMinutes(self::LOCAL_OFFSET) : 0;
        $zone = $m[8] ?? '';
        if ($zone === 'Z') {
            $offsetMinutes = 0;
        } elseif ($zone !== '') {
            $sign = $zone[0] === '-' ? -1 : 1;
            $offsetMinutes = $sign * ((int) substr($zone, 1, 2) * 60 + (int) substr($zone, 4, 2));
        }
        $instant = ZonedInstant::fromLocal(
            (int) $m[1],
            (int) $m[2],
            (int) $m[3],
            (int) ($m[4] ?? 0),
            (int) ($m[5] ?? 0),
            (int) (($m[6] ?? '') !== '' ? $m[6] : 0),
            (int) substr(($m[7] ?? '') . '000', 0, 3),
            $offsetMinutes,
        );
        return self::fromEpochMilliseconds($instant->epochMs, 0.0);
    }

    /** cql-execution's DateTime.fromJSDate: an instant read at an offset. */
    public static function fromEpochMilliseconds(int $epochMs, float $timezoneOffset): self
    {
        $utc = ZonedInstant::fromLocal(1970)->plus(Precision::Millisecond, $epochMs);
        return self::fromZonedAt($utc->withOffset(self::offsetMinutes($timezoneOffset)), $timezoneOffset);
    }

    public static function fields(): array
    {
        return Precision::dateTimeFields();
    }

    public function field(Precision $field): ?int
    {
        return match ($field) {
            Precision::Year => $this->year,
            Precision::Month => $this->month,
            Precision::Day => $this->day,
            Precision::Hour => $this->hour,
            Precision::Minute => $this->minute,
            Precision::Second => $this->second,
            Precision::Millisecond => $this->millisecond,
            Precision::Week => null,
        };
    }

    public function getPrecision(): ?Precision
    {
        $result = null;
        foreach (self::fields() as $field) {
            if ($this->field($field) === null) {
                return $result;
            }
            $result = $field;
        }
        return $result;
    }

    public function isTime(): bool
    {
        return $this->year === 0 && $this->month === 1 && $this->day === 1;
    }

    public function isUTC(): bool
    {
        return $this->timezoneOffset === null || $this->timezoneOffset == 0;
    }

    public function withTimezoneOffset(?float $timezoneOffset): self
    {
        return new self($this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second, $this->millisecond, $timezoneOffset);
    }

    public function withMillisecond(?int $millisecond): self
    {
        return new self($this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second, $millisecond, $this->timezoneOffset);
    }

    public function reducedPrecision(?Precision $precision = null): static
    {
        $precision ??= Precision::Millisecond;
        $keep = static fn (Precision $field): bool => $field->value === $precision->value
            || array_search($field, self::fields(), true) < array_search($precision, self::fields(), true);
        return new self(
            $this->year,
            $keep(Precision::Month) ? $this->month : null,
            $keep(Precision::Day) ? $this->day : null,
            $keep(Precision::Hour) ? $this->hour : null,
            $keep(Precision::Minute) ? $this->minute : null,
            $keep(Precision::Second) ? $this->second : null,
            $keep(Precision::Millisecond) ? $this->millisecond : null,
            $this->timezoneOffset,
        );
    }

    /**
     * The same moment at another offset, at this value's precision. An
     * imprecise value is shifted from its earliest moment, so the year of
     * "2025" can change.
     */
    public function convertToTimezoneOffset(?float $timezoneOffset = 0.0): self
    {
        $shifted = $this->toZoned()->withOffset(self::offsetMinutes($timezoneOffset ?? 0.0));
        return self::fromZonedAt($shifted, ($timezoneOffset ?? 0.0))->reducedPrecision($this->getPrecision());
    }

    /**
     * The number of whole unit boundaries crossed between this value and
     * another (CQL "difference in"). An imprecise value gives a range.
     */
    public function differenceBetween(mixed $other, Precision $unit): ?Uncertainty
    {
        $other = self::implicitlyConvert($other);
        if (!$other instanceof self) {
            return null;
        }
        [$thisStart, $thisEnd] = $this->zonedRange();
        [$otherStart, $otherEnd] = $other->zonedRange();
        // Days and coarser ignore offsets so they count calendar days.
        if (in_array($unit, [Precision::Year, Precision::Month, Precision::Week, Precision::Day], true)) {
            [$thisStart, $thisEnd, $otherStart, $otherEnd] = array_map(
                static fn (ZonedInstant $i): ZonedInstant => $i->toUtcKeepingLocalTime(),
                [$thisStart, $thisEnd, $otherStart, $otherEnd],
            );
        }
        [$thisStart, $thisEnd, $otherStart, $otherEnd] = array_map(
            static fn (ZonedInstant $i): ZonedInstant => $i->truncate($unit),
            [$thisStart, $thisEnd, $otherStart, $otherEnd],
        );
        return new Uncertainty($otherStart->wholeUnitsSince($thisEnd, $unit), $otherEnd->wholeUnitsSince($thisStart, $unit));
    }

    /**
     * The number of whole units elapsed between this value and another (CQL
     * "duration in"). An imprecise value gives a range.
     */
    public function durationBetween(mixed $other, Precision $unit): ?Uncertainty
    {
        $other = self::implicitlyConvert($other);
        if (!$other instanceof self) {
            return null;
        }
        // Seconds and milliseconds are one decimal precision: a missing
        // millisecond is zero, not unknown.
        $a = $this->second !== null && $this->millisecond === null && $unit !== Precision::Millisecond
            ? $this->withMillisecond(0) : $this;
        $b = $other->second !== null && $other->millisecond === null && $unit !== Precision::Millisecond
            ? $other->withMillisecond(0) : $other;
        [$thisStart, $thisEnd] = $a->zonedRange();
        [$otherStart, $otherEnd] = $b->zonedRange();
        return new Uncertainty($otherStart->wholeUnitsSince($thisEnd, $unit), $otherEnd->wholeUnitsSince($thisStart, $unit));
    }

    public function getDateTime(): self
    {
        return $this;
    }

    public function getDate(): CqlDate
    {
        return new CqlDate($this->year, $this->month, $this->day);
    }

    public function getTime(): self
    {
        return new self(0, 1, 1, $this->hour, $this->minute, $this->second, $this->millisecond, null);
    }

    public function toString(): string
    {
        return $this->isTime() ? $this->toStringTime() : $this->toStringDateTime();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected function toZoned(): ZonedInstant
    {
        return ZonedInstant::fromLocal(
            $this->year ?? 1970,
            $this->month ?? 1,
            $this->day ?? 1,
            $this->hour ?? 0,
            $this->minute ?? 0,
            $this->second ?? 0,
            $this->millisecond ?? 0,
            self::offsetMinutes($this->timezoneOffset ?? self::LOCAL_OFFSET),
        );
    }

    protected static function fromZoned(ZonedInstant $instant): static
    {
        return self::fromZonedAt($instant, $instant->offsetMinutes / 60);
    }

    protected function keepTimezoneOffsetOf(CqlTemporal $result): static
    {
        return $this->timezoneOffset === null ? $result->withTimezoneOffset(null) : $result;
    }

    protected function convertedForComparison(CqlTemporal $other): static
    {
        if (!$other instanceof self) {
            throw new \LogicException('Type mismatch');
        }
        return $other->timezoneOffset === $this->timezoneOffset
            ? $other
            : $other->convertToTimezoneOffset($this->timezoneOffset);
    }

    /**
     * @return array{ZonedInstant, ZonedInstant} the earliest and latest moments this value can be
     */
    private function zonedRange(): array
    {
        $low = $this->toZoned();
        $precision = $this->getPrecision() ?? Precision::Millisecond;
        return [$low, $low->endOf($precision)];
    }

    private static function implicitlyConvert(mixed $other): mixed
    {
        return $other instanceof CqlDate ? $other->getDateTime() : $other;
    }

    private static function fromZonedAt(ZonedInstant $instant, float $timezoneOffset): self
    {
        return new self(
            $instant->year,
            $instant->month,
            $instant->day,
            $instant->hour,
            $instant->minute,
            $instant->second,
            $instant->millisecond,
            $timezoneOffset,
        );
    }

    private static function offsetMinutes(float $hours): int
    {
        return (int) round($hours * 60);
    }

    private static function replaceMilliseconds(string $string, string $milliseconds): string
    {
        $dot = strpos($string, '.');
        if ($dot === false) {
            return $string;
        }
        $after = substr($string, $dot + 1);
        $zone = '';
        foreach (['Z', '+', '-'] as $separator) {
            $at = strpos($after, $separator);
            if ($at !== false) {
                $zone = substr($after, $at);
                break;
            }
        }
        return substr($string, 0, $dot) . '.' . $milliseconds . $zone;
    }

    private static function isValidString(string $string): bool
    {
        // Luxon reads +hh only as +hh:00.
        if (preg_match('/T[\d:.]*[+-]\d{2}$/', $string) === 1) {
            $string .= ':00';
        }
        $shapeMatches = false;
        foreach (self::VALID_SHAPES as $shape) {
            if (preg_match($shape, $string) === 1) {
                $shapeMatches = true;
                break;
            }
        }
        if (!$shapeMatches) {
            return false;
        }
        $month = strlen($string) >= 7 ? (int) substr($string, 5, 2) : 1;
        $day = strlen($string) >= 10 ? (int) substr($string, 8, 2) : 1;
        if ($month < 1 || $month > 12 || $day < 1 || $day > ZonedInstant::daysInMonth((int) substr($string, 0, 4), $month)) {
            return false;
        }
        if (preg_match('/T(\d{2})(?::(\d{2}))?(?::(\d{2}))?(?:\.(\d{3}))?/', $string, $time) === 1) {
            $hour = (int) $time[1];
            $minute = (int) ($time[2] ?? 0);
            $second = (int) ($time[3] ?? 0);
            $millisecond = (int) ($time[4] ?? 0);
            // Luxon accepts 24:00 as the end of the day, and nothing else past 23.
            $validHour = $hour <= 23 || ($hour === 24 && $minute === 0 && $second === 0 && $millisecond === 0);
            if (!$validHour || $minute > 59 || $second > 59) {
                return false;
            }
        }
        return true;
    }

    private function toStringTime(): string
    {
        $str = '';
        if ($this->hour !== null) {
            $str .= sprintf('%02d', $this->hour);
            if ($this->minute !== null) {
                $str .= sprintf(':%02d', $this->minute);
                if ($this->second !== null) {
                    $str .= sprintf(':%02d', $this->second);
                    if ($this->millisecond !== null) {
                        $str .= sprintf('.%03d', $this->millisecond);
                    }
                }
            }
        }
        return $str;
    }

    private function toStringDateTime(): string
    {
        $str = '';
        $parts = [
            [$this->year, '%04d'],
            [$this->month, '-%02d'],
            [$this->day, '-%02d'],
            [$this->hour, 'T%02d'],
            [$this->minute, ':%02d'],
            [$this->second, ':%02d'],
            [$this->millisecond, '.%03d'],
        ];
        foreach ($parts as [$value, $format]) {
            if ($value === null) {
                break;
            }
            $str .= sprintf($format, $value);
        }
        if (str_contains($str, 'T') && $this->timezoneOffset !== null) {
            $absolute = abs($this->timezoneOffset);
            $hours = (int) floor($absolute);
            $minutes = ($absolute - $hours) * 60;
            $str .= ($this->timezoneOffset < 0 ? '-' : '+') . sprintf('%02d', $hours) . ':' . self::padJsNumber($minutes);
        }
        return $str;
    }

    /** String(n).padStart(2, '0') for a minute count that may be fractional. */
    private static function padJsNumber(float $minutes): string
    {
        $text = $minutes == floor($minutes) ? (string) (int) $minutes : (string) $minutes;
        return str_pad($text, 2, '0', STR_PAD_LEFT);
    }
}
