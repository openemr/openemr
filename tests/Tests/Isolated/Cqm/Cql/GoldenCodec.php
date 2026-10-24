<?php

/**
 * Reads the golden fixtures that golden-units.js and golden-intervals.js
 * write, and encodes PHP results the same way for exact comparison:
 * numbers as "#" and JavaScript's string form, dates as "dt|..." and
 * "d|...", and quantities, ratios, uncertainties, intervals and UCUM units
 * as single-key objects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Cql;

use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Ratio;
use OpenEMR\Cqm\Cql\Types\Uncertainty;
use OpenEMR\Cqm\Cql\Ucum\UcumUnit;
use OpenEMR\Cqm\Cql\Util\JavaScript;

trait GoldenCodec
{
    /**
     * @return list<array{string, list<mixed>, mixed}> every case of the fixture files in a directory
     */
    private static function loadGoldenCases(string $directory): array
    {
        $files = glob($directory . '/*.json') ?: [];
        if ($files === []) {
            throw new \RuntimeException("No golden files in $directory");
        }
        $cases = [];
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data = $json === false ? null : json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || !is_array($data['cases'] ?? null)) {
                throw new \UnexpectedValueException("Malformed golden file $file");
            }
            foreach ($data['cases'] as $case) {
                if (!is_array($case) || !is_string($case[0] ?? null) || !is_array($case[1] ?? null)) {
                    throw new \UnexpectedValueException('Malformed golden case');
                }
                $cases[] = [$case[0], array_values($case[1]), $case[2] ?? null];
            }
        }
        return $cases;
    }

    /**
     * @param list<mixed> $args
     * @return list<mixed>
     */
    private static function decodeAll(array $args): array
    {
        // A plain loop: array_map with a first-class callable here
        // crashes PHP 8.3.6 when a decoded argument throws.
        $decoded = [];
        foreach ($args as $arg) {
            $decoded[] = self::decode($arg);
        }
        return $decoded;
    }

    /** Rebuilds a value written by the generators; anything else is as written. */
    private static function decode(mixed $value): mixed
    {
        if (is_string($value) && str_starts_with($value, '#')) {
            return JavaScript::toNumber(substr($value, 1));
        }
        if (is_string($value) && preg_match('/^(dt|d)\|/', $value, $kind) === 1) {
            $parts = explode('|', $value);
            $int = static fn (string $v): ?int => $v === '' ? null : (int) $v;
            if ($kind[1] === 'd') {
                return new CqlDate($int($parts[1]), $int($parts[2]), $int($parts[3]));
            }
            return new CqlDateTime(
                $int($parts[1]),
                $int($parts[2]),
                $int($parts[3]),
                $int($parts[4]),
                $int($parts[5]),
                $int($parts[6]),
                $int($parts[7]),
                $parts[8] === '' ? null : (float) $parts[8],
            );
        }
        if (!is_array($value) || count($value) !== 1) {
            return $value;
        }
        $parts = reset($value);
        if (!is_array($parts)) {
            return $value;
        }
        return match (key($value)) {
            'q' => new Quantity(self::goldenNumber(self::decode($parts[0] ?? null)), self::goldenNullableString($parts[1] ?? null)),
            'r' => new Ratio(self::goldenQuantity(self::decode($parts[0] ?? null)), self::goldenQuantity(self::decode($parts[1] ?? null))),
            'u' => new Uncertainty(self::decode($parts[0] ?? null), self::decode($parts[1] ?? null)),
            'i' => new Interval(
                self::decode($parts[0] ?? null),
                self::decode($parts[1] ?? null),
                self::goldenBool($parts[2] ?? null),
                self::goldenBool($parts[3] ?? null),
                self::goldenNullableString($parts[4] ?? null),
            ),
            default => $value,
        };
    }

    private static function encode(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return '#' . JavaScript::numberToString($value);
        }
        if (is_array($value)) {
            $encoded = [];
            foreach ($value as $key => $item) {
                $encoded[$key] = self::encode($item);
            }
            return $encoded;
        }
        if ($value instanceof Interval) {
            return ['i' => [self::encode($value->low), self::encode($value->high), $value->lowClosed, $value->highClosed, $value->defaultPointType]];
        }
        if ($value instanceof Uncertainty) {
            return ['u' => [self::encode($value->low), self::encode($value->high)]];
        }
        if ($value instanceof UcumUnit) {
            return ['u' => [
                $value->csCode,
                '#' . JavaScript::numberToString($value->magnitude),
                $value->dim,
                $value->cnv,
                '#' . JavaScript::numberToString($value->cnvPfx),
                $value->isSpecial,
                $value->isArbitrary,
                $value->moleExp,
                $value->equivalentExp,
            ]];
        }
        if ($value instanceof Quantity) {
            return ['q' => ['#' . JavaScript::numberToString($value->value), $value->unit]];
        }
        if ($value instanceof Ratio) {
            return ['r' => [self::encode($value->numerator), self::encode($value->denominator)]];
        }
        if ($value instanceof CqlDateTime) {
            $fields = [$value->year, $value->month, $value->day, $value->hour, $value->minute, $value->second, $value->millisecond];
            $offset = $value->timezoneOffset;
            $offsetText = $offset === null ? '' : JavaScript::numberToString($offset);
            return implode('|', ['dt', ...array_map(static fn (?int $f): string => (string) $f, $fields), $offsetText]);
        }
        if ($value instanceof CqlDate) {
            return implode('|', ['d', (string) $value->year, (string) $value->month, (string) $value->day]);
        }
        return $value;
    }

    private static function goldenQuantity(mixed $value): Quantity
    {
        return $value instanceof Quantity ? $value : throw new \UnexpectedValueException('Not a quantity');
    }

    private static function goldenString(mixed $value): string
    {
        return is_string($value) ? $value : throw new \UnexpectedValueException('Not a string');
    }

    private static function goldenNullableString(mixed $value): ?string
    {
        return $value === null ? null : self::goldenString($value);
    }

    private static function goldenBool(mixed $value): bool
    {
        return is_bool($value) ? $value : throw new \UnexpectedValueException('Not a boolean');
    }

    private static function goldenNumber(mixed $value): float
    {
        return is_int($value) || is_float($value) ? (float) $value : throw new \UnexpectedValueException('Not a number');
    }
}
