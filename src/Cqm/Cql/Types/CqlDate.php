<?php

/**
 * The CQL Date type (no time, no offset), ported from cql-execution 3.3.2.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final class CqlDate extends CqlTemporal implements \Stringable
{
    public function __construct(
        public readonly ?int $year = null,
        public readonly ?int $month = null,
        public readonly ?int $day = null,
    ) {
    }

    /** Parses yyyy, yyyy-MM or yyyy-MM-dd, or returns null. */
    public static function parse(string $string): ?self
    {
        if (preg_match('/(\d{4})(-(\d{2}))?(-(\d{2}))?/', $string, $m, PREG_UNMATCHED_AS_NULL) !== 1) {
            return null;
        }
        if (preg_match('/^\d{4}(-\d{2}(-\d{2})?)?$/', $string) !== 1) {
            return null;
        }
        $year = (int) $m[1];
        $month = $m[3] !== null ? (int) $m[3] : null;
        $day = $m[5] !== null ? (int) $m[5] : null;
        if ($month !== null && ($month < 1 || $month > 12)) {
            return null;
        }
        if ($day !== null && ($day < 1 || $day > ZonedInstant::daysInMonth($year, $month ?? 1))) {
            return null;
        }
        return new self($year, $month, $day);
    }

    public static function fields(): array
    {
        return Precision::dateFields();
    }

    public function field(Precision $field): ?int
    {
        return match ($field) {
            Precision::Year => $this->year,
            Precision::Month => $this->month,
            Precision::Day => $this->day,
            Precision::Week, Precision::Hour, Precision::Minute, Precision::Second, Precision::Millisecond => null,
        };
    }

    public function getPrecision(): ?Precision
    {
        if ($this->year === null) {
            return null;
        }
        if ($this->month === null) {
            return Precision::Year;
        }
        return $this->day === null ? Precision::Month : Precision::Day;
    }

    public function reducedPrecision(?Precision $precision = null): static
    {
        $precision ??= Precision::Day;
        return match ($precision) {
            Precision::Year => new self($this->year),
            Precision::Month => new self($this->year, $this->month),
            default => new self($this->year, $this->month, $this->day),
        };
    }

    public function differenceBetween(mixed $other, Precision $unit): ?Uncertainty
    {
        if ($other instanceof CqlDateTime) {
            return $this->getDateTime()->differenceBetween($other, $unit);
        }
        if (!$other instanceof self) {
            return null;
        }
        [$thisStart, $thisEnd] = array_map(static fn (ZonedInstant $i): ZonedInstant => $i->truncate($unit), $this->zonedRange());
        [$otherStart, $otherEnd] = array_map(static fn (ZonedInstant $i): ZonedInstant => $i->truncate($unit), $other->zonedRange());
        return new Uncertainty($otherStart->wholeUnitsSince($thisEnd, $unit), $otherEnd->wholeUnitsSince($thisStart, $unit));
    }

    public function durationBetween(mixed $other, Precision $unit): ?Uncertainty
    {
        if ($other instanceof CqlDateTime) {
            return $this->getDateTime()->durationBetween($other, $unit);
        }
        if (!$other instanceof self) {
            return null;
        }
        [$thisStart, $thisEnd] = $this->zonedRange();
        [$otherStart, $otherEnd] = $other->zonedRange();
        return new Uncertainty($otherStart->wholeUnitsSince($thisEnd, $unit), $otherEnd->wholeUnitsSince($thisStart, $unit));
    }

    /**
     * The DateTime of this date with no time; it takes the evaluation
     * timezone offset (see CqlDateTime::LOCAL_OFFSET).
     */
    public function getDateTime(float $timezoneOffset = CqlDateTime::LOCAL_OFFSET): CqlDateTime
    {
        if ($this->year !== null && $this->month !== null && $this->day !== null) {
            return new CqlDateTime($this->year, $this->month, $this->day, null, null, null, null, $timezoneOffset);
        }
        return new CqlDateTime($this->year, $this->month, $this->day);
    }

    public function toString(): string
    {
        $str = '';
        foreach ([[$this->year, '%04d'], [$this->month, '-%02d'], [$this->day, '-%02d']] as [$value, $format]) {
            if ($value === null) {
                break;
            }
            $str .= sprintf($format, $value);
        }
        return $str;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected function toZoned(): ZonedInstant
    {
        return ZonedInstant::fromLocal($this->year ?? 1970, $this->month ?? 1, $this->day ?? 1);
    }

    protected static function fromZoned(ZonedInstant $instant): static
    {
        return new self($instant->year, $instant->month, $instant->day);
    }

    /**
     * @return array{ZonedInstant, ZonedInstant} the first and last days this value can be
     */
    private function zonedRange(): array
    {
        $low = $this->toZoned();
        $precision = $this->getPrecision() ?? Precision::Day;
        return [$low, $low->endOf($precision)->startOf(Precision::Day)];
    }
}
