<?php

/**
 * A key that is equal for values cql-execution's distinct treats as the
 * same (its toNormalizedKey): dates compared in UTC, intervals closed,
 * uncertainties that are points as the point, and objects by their
 * properties in any order.
 *
 * cql-execution keys a Quantity by its value converted to the first unit
 * UCUM lists as commensurable; this keys it by value and unit as written,
 * so quantities equal only after conversion stay distinct.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Qdm\QdmObject;
use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Ratio;
use OpenEMR\Cqm\Cql\Types\Tuple;
use OpenEMR\Cqm\Cql\Types\Uncertainty;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final class DistinctKey
{
    public static function of(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => 'n' . JavaScript::numberToString($value),
            is_string($value) => 's' . strlen($value) . ':' . $value,
            is_array($value) => 'L[' . implode(',', array_map(self::of(...), $value)) . ']',
            $value instanceof Code => 'Code' . self::map(['code' => $value->code, 'system' => $value->system, 'version' => $value->version, 'display' => $value->display]),
            $value instanceof CqlDateTime => 'DateTime' . self::dateTime($value),
            $value instanceof CqlDate => 'Date' . self::map(['year' => $value->year, 'month' => $value->month, 'day' => $value->day]),
            $value instanceof Interval => self::interval($value),
            $value instanceof Quantity => 'Quantity' . self::map(['value' => $value->value, 'unit' => $value->unit === '' ? null : $value->unit]),
            $value instanceof Ratio => 'Ratio' . self::map(['numerator' => $value->numerator, 'denominator' => $value->denominator]),
            $value instanceof Uncertainty => $value->isPoint() ? self::of($value->low) : 'Uncertainty' . self::map(['low' => $value->low, 'high' => $value->high]),
            $value instanceof Tuple => 'Object' . self::map($value->elements),
            $value instanceof QdmObject => ($value->isDataElement ? implode('|', $value->typeHierarchy()) : 'Object') . self::map($value->fields),
            is_object($value) => $value::class . self::map(get_object_vars($value)),
            default => 'unknown',
        };
    }

    private static function dateTime(CqlDateTime $value): string
    {
        $offset = $value->timezoneOffset;
        if ($offset !== null && $offset != 0) {
            $value = $value->convertToTimezoneOffset(0);
        }
        return self::map([
            'year' => $value->year,
            'month' => $value->month,
            'day' => $value->day,
            'hour' => $value->hour,
            'minute' => $value->minute,
            'second' => $value->second,
            'millisecond' => $value->millisecond,
            'timezoneOffset' => $value->timezoneOffset,
        ]);
    }

    private static function interval(Interval $value): string
    {
        $closed = $value->toClosed();
        return 'Interval' . self::map([
            'low' => $closed->low,
            'high' => $closed->high,
            'lowClosed' => $closed->lowClosed,
            'highClosed' => $closed->highClosed,
            'defaultPointType' => $closed->defaultPointType,
        ]);
    }

    /**
     * @param array<array-key, mixed> $properties
     */
    private static function map(array $properties): string
    {
        ksort($properties, SORT_STRING);
        $parts = [];
        foreach ($properties as $name => $item) {
            $parts[] = $name . '=' . self::of($item);
        }
        return '{' . implode(',', $parts) . '}';
    }
}
