<?php

/**
 * The ELM Collapse and Expand operators over lists of intervals, ported
 * from cql-execution 3.3.2's elm/interval.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\CqlTemporal;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Precision;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Util\CqlMath;
use OpenEMR\Cqm\Cql\Util\JavaScript;
use OpenEMR\Cqm\Cql\Util\Units;

final class IntervalLists
{
    /**
     * Merges intervals that overlap or are within one point (or per) of
     * each other.
     *
     * @return list<Interval>|null
     */
    public static function collapse(mixed $intervals, mixed $per): ?array
    {
        if ($intervals === null) {
            return null;
        }
        if (!is_array($intervals)) {
            throw new \UnexpectedValueException('Collapse needs a list of intervals');
        }
        $clones = [];
        foreach ($intervals as $interval) {
            if ($interval !== null) {
                $clones[] = $interval instanceof Interval ? $interval->copy() : throw new \UnexpectedValueException('Not an interval');
            }
        }
        if (count($clones) <= 1) {
            return $clones;
        }
        $per = $per instanceof Quantity ? $per : $clones[0]->getPointSize();
        usort($clones, self::compareForCollapse(...));
        $collapsed = [];
        $a = array_shift($clones);
        $b = array_shift($clones);
        while ($b !== null) {
            if ($b->low instanceof CqlTemporal) {
                if ($a->high instanceof CqlTemporal && $a->high->sameOrAfter($b->low) === true) {
                    if ($b->high === null || ($b->high instanceof CqlTemporal && $b->high->after($a->high) === true)) {
                        $a = new Interval($a->low, $b->high, $a->lowClosed, $a->highClosed);
                    }
                } elseif (self::gapWithin($a->high, $b->low, $per)) {
                    $a = new Interval($a->low, $b->high, $a->lowClosed, $a->highClosed);
                } else {
                    $collapsed[] = $a;
                    $a = $b;
                }
            } elseif ($b->low instanceof Quantity) {
                $reach = $a->high instanceof Quantity ? Quantity::doAddition($a->high, $per) : null;
                if ($a->high !== null && $b->low->sameOrBefore($reach) === true) {
                    if ($b->high === null || ($b->high instanceof Quantity && $b->high->after($a->high) === true)) {
                        $a = new Interval($a->low, $b->high, $a->lowClosed, $a->highClosed);
                    }
                } else {
                    $collapsed[] = $a;
                    $a = $b;
                }
            } elseif (self::number($b->low) - self::number($a->high) <= $per->value) {
                if ($b->high === null || self::number($b->high) > self::number($a->high)) {
                    $a = new Interval($a->low, $b->high, $a->lowClosed, $a->highClosed);
                }
            } else {
                $collapsed[] = $a;
                $a = $b;
            }
            $b = array_shift($clones);
        }
        $collapsed[] = $a;
        return $collapsed;
    }

    /**
     * Splits intervals into intervals of per (by default one point of
     * their own precision or unit).
     *
     * @return list<Interval>|null
     */
    public static function expand(mixed $intervals, mixed $per): ?array
    {
        $list = is_array($intervals) ? array_values($intervals) : [$intervals];
        $type = self::listType($list);
        if ($type === 'mismatch') {
            throw new \UnexpectedValueException('List of intervals contains mismatched types.');
        }
        if ($type === null) {
            return null;
        }
        $collapsed = self::collapse($list, $per) ?? [];
        if ($collapsed === []) {
            return [];
        }
        $per = $per instanceof Quantity ? $per : null;
        $results = [];
        foreach ($collapsed as $interval) {
            if ($interval->low === null || $interval->high === null) {
                return null;
            }
            if ($type === 'datetime') {
                $interval = new Interval(
                    self::dateTimeOf($interval->low),
                    self::dateTimeOf($interval->high),
                    $interval->lowClosed,
                    $interval->highClosed,
                );
            }
            $per ??= match ($type) {
                'date', 'datetime' => new Quantity(1, $interval->low instanceof CqlTemporal ? $interval->low->getPrecision()?->value : null),
                'quantity' => new Quantity(1, $interval->low instanceof Quantity ? $interval->low->unit : null),
                default => new Quantity(1, '1'),
            };
            $items = match ($type) {
                'date', 'datetime' => self::expandDates($interval, $per),
                'quantity' => self::expandQuantities($interval, $per),
                default => self::expandNumbers($interval, $per),
            };
            if ($items === null) {
                return null;
            }
            array_push($results, ...$items);
        }
        return $results;
    }

    private static function compareForCollapse(Interval $a, Interval $b): int
    {
        if (($a->low instanceof CqlTemporal || $a->low instanceof Quantity)) {
            if ($b->low !== null && $a->low->before($b->low) === true) {
                return -1;
            }
            if ($b->low === null || $a->low->after($b->low) === true) {
                return 1;
            }
        } elseif ($a->low !== null && $b->low !== null) {
            if (self::number($a->low) < self::number($b->low)) {
                return -1;
            }
            if (self::number($a->low) > self::number($b->low)) {
                return 1;
            }
        } elseif ($a->low !== null) {
            return 1;
        } elseif ($b->low !== null) {
            return -1;
        }
        if ($a->high instanceof CqlTemporal || $a->high instanceof Quantity) {
            if ($b->high === null || $a->high->before($b->high) === true) {
                return -1;
            }
            if ($a->high->after($b->high) === true) {
                return 1;
            }
        } elseif ($a->high !== null && $b->high !== null) {
            if (self::number($a->high) < self::number($b->high)) {
                return -1;
            }
            if (self::number($a->high) > self::number($b->high)) {
                return 1;
            }
        } elseif ($a->high !== null) {
            return -1;
        } elseif ($b->high !== null) {
            return 1;
        }
        return 0;
    }

    /** Whether the gap from a's high to b's low is no more than per. */
    private static function gapWithin(mixed $high, CqlTemporal $low, Quantity $per): bool
    {
        if (!$high instanceof CqlTemporal) {
            return false;
        }
        $unit = Precision::tryFrom(Units::convertToCqlDateUnit($per->unit) ?? (string) $per->unit)
            ?? throw new \UnexpectedValueException('Invalid unit for collapse');
        $gap = $high->durationBetween($low, $unit);
        $gapHigh = $gap?->high;
        return (is_int($gapHigh) || is_float($gapHigh)) && $gapHigh <= $per->value;
    }

    /**
     * @param list<mixed> $intervals
     */
    private static function listType(array $intervals): ?string
    {
        $type = null;
        foreach ($intervals as $interval) {
            if (!$interval instanceof Interval || ($interval->low === null && $interval->high === null)) {
                continue;
            }
            $low = $interval->low ?? $interval->high;
            $high = $interval->high ?? $interval->low;
            if (($low instanceof CqlDateTime || $high instanceof CqlDateTime)
                && ($low instanceof CqlDateTime || $low instanceof CqlDate)
                && ($high instanceof CqlDateTime || $high instanceof CqlDate)) {
                if ($type === null || $type === 'date') {
                    $type = 'datetime';
                } elseif ($type !== 'datetime') {
                    return 'mismatch';
                }
            } elseif ($low instanceof CqlDate && $high instanceof CqlDate) {
                if ($type === null) {
                    $type = 'date';
                } elseif ($type !== 'date' && $type !== 'datetime') {
                    return 'mismatch';
                }
            } elseif ($low instanceof Quantity && $high instanceof Quantity) {
                if ($type === null) {
                    $type = 'quantity';
                } elseif ($type !== 'quantity') {
                    return 'mismatch';
                }
            } elseif ((is_int($low) || is_float($low)) && (is_int($high) || is_float($high))) {
                $whole = CqlMath::isWhole($low) && CqlMath::isWhole($high);
                if ($whole) {
                    if ($type === null) {
                        $type = 'integer';
                    } elseif ($type !== 'integer' && $type !== 'decimal') {
                        return 'mismatch';
                    }
                } elseif ($type === null || $type === 'integer') {
                    $type = 'decimal';
                } elseif ($type !== 'decimal') {
                    return 'mismatch';
                }
            } else {
                return 'mismatch';
            }
        }
        return $type;
    }

    private static function dateTimeOf(mixed $value): mixed
    {
        return $value instanceof CqlDate ? $value->getDateTime() : $value;
    }

    /**
     * @return list<Interval>|null
     */
    private static function expandDates(Interval $interval, Quantity &$per): ?array
    {
        // cql-execution converts per in place, so later intervals see it converted.
        $unitName = Units::convertToCqlDateUnit($per->unit);
        $value = $per->value;
        if ($unitName === 'week') {
            $value *= 7;
            $unitName = 'day';
        }
        $per = new Quantity($value, $unitName);
        $low = $interval->low;
        $high = $interval->high;
        if (!$low instanceof CqlTemporal || !$high instanceof CqlTemporal) {
            return null;
        }
        $unit = $unitName === null ? null : Precision::tryFrom($unitName);
        if ($unit === null || !in_array($unit, $low::fields(), true)) {
            return null;
        }
        $start = $interval->lowClosed ? $low : $low->successor();
        $end = $interval->highClosed ? $high : $high->predecessor();
        if ($start === null || $end === null) {
            return null;
        }
        if ($start->after($end) === true) {
            return [];
        }
        if ($low->isLessPrecise($unit) || $high->isLessPrecise($unit)) {
            return [];
        }
        // The start is truncated in place, so the first interval starts truncated too.
        $currentLow = $start->reducedPrecision($unit);
        $end = $end->reducedPrecision($unit);
        $results = [];
        $currentHigh = $currentLow->add($value, $unit)?->predecessor();
        while ($currentHigh !== null && $currentHigh->sameOrBefore($end) === true) {
            $results[] = new Interval($currentLow, $currentHigh, true, true);
            $currentLow = $currentLow->add($value, $unit);
            $currentHigh = $currentLow->add($value, $unit)?->predecessor();
        }
        return $results;
    }

    /**
     * @return list<Interval>|null
     */
    private static function expandQuantities(Interval $interval, Quantity $per): ?array
    {
        $low = $interval->low;
        $high = $interval->high;
        if (!$low instanceof Quantity || !$high instanceof Quantity) {
            return null;
        }
        $units = Units::compareUnits($low->unit, $per->unit) > 0 ? $per->unit : $low->unit;
        $lowValue = Units::convertUnit($low->value, $low->unit, $units);
        $highValue = Units::convertUnit($high->value, $high->unit, $units);
        $perValue = Units::convertUnit($per->value, $per->unit, $units);
        if ($lowValue === null || $highValue === null || $perValue === null) {
            return null;
        }
        $results = [];
        foreach (self::numericIntervals($lowValue, $highValue, $interval->lowClosed, $interval->highClosed, $perValue) as $item) {
            $results[] = new Interval(
                new Quantity(self::number($item->low), $units),
                new Quantity(self::number($item->high), $units),
                true,
                true,
            );
        }
        return $results;
    }

    /**
     * @return list<Interval>|null
     */
    private static function expandNumbers(Interval $interval, Quantity $per): ?array
    {
        if ($per->unit !== '1' && $per->unit !== '') {
            return null;
        }
        return self::numericIntervals(self::number($interval->low), self::number($interval->high), $interval->lowClosed, $interval->highClosed, $per->value);
    }

    /**
     * @return list<Interval>
     */
    private static function numericIntervals(int|float $low, int|float $high, bool $lowClosed, bool $highClosed, float $perValue): array
    {
        $perIsDecimal = str_contains(JavaScript::numberToString($perValue), '.');
        $places = $perIsDecimal ? 8 : 0;
        $low = $lowClosed ? $low : self::number(CqlMath::successor($low));
        $high = $highClosed ? $high : self::number(CqlMath::predecessor($high));
        $low = self::truncateDecimal($low, $places);
        $high = self::truncateDecimal($high, $places);
        if ($low > $high) {
            return [];
        }
        $unitSize = $perIsDecimal ? 0.00000001 : 1;
        if ($low == $high && CqlMath::isWhole($low) && CqlMath::isWhole($high) && !CqlMath::isWhole($perValue)) {
            $high = self::toFixed($high + 1, $places);
        }
        if ($perValue > $high - $low + $unitSize) {
            return [];
        }
        $results = [];
        $currentLow = $low;
        $currentHigh = self::toFixed($currentLow + $perValue - $unitSize, $places);
        while ($currentHigh <= $high) {
            $results[] = new Interval($currentLow, $currentHigh, true, true);
            $currentLow = self::toFixed($currentLow + $perValue, $places);
            $currentHigh = self::toFixed($currentLow + $perValue - $unitSize, $places);
        }
        return $results;
    }

    /** parseFloat(x.toFixed(places)). */
    private static function toFixed(int|float $value, int $places): float
    {
        return (float) sprintf('%.' . $places . 'F', $value);
    }

    /** cql-execution's truncateDecimal: cut the string form to the given places. */
    private static function truncateDecimal(int|float $value, int $places): float
    {
        $text = JavaScript::numberToString($value);
        if ($places === 0) {
            return JavaScript::parseFloat((string) preg_replace('/^(-?\d+).*$/s', '$1', $text));
        }
        if (preg_match('/^-?\d+(?:.\d{0,' . $places . '})?/', $text, $m) === 1) {
            return JavaScript::parseFloat($m[0]);
        }
        return NAN;
    }

    private static function number(mixed $value): int|float
    {
        return match (true) {
            is_int($value), is_float($value) => $value,
            $value === null, $value === false => 0,
            $value === true => 1,
            default => NAN,
        };
    }
}
