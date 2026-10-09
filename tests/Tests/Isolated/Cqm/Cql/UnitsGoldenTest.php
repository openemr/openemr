<?php

/**
 * Replays results recorded from ucum-lhc and cql-execution (see
 * golden-units.js) against the PHP UCUM port, the CQL unit helpers, and
 * the Quantity and Ratio types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Cql;

use OpenEMR\Cqm\Cql\Types\Comparison;
use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\CqlTemporal;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Ratio;
use OpenEMR\Cqm\Cql\Ucum\Ucum;
use OpenEMR\Cqm\Cql\Ucum\UcumUnit;
use OpenEMR\Cqm\Cql\Util\CqlMath;
use OpenEMR\Cqm\Cql\Util\JavaScript;
use OpenEMR\Cqm\Cql\Util\Units;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UnitsGoldenTest extends TestCase
{
    /** @var list<array{string, list<mixed>, mixed}>|null */
    private static ?array $cases = null;

    /**
     * One test per operation, so a failure names the operation; each
     * reports every mismatching case up to a limit.
     */
    #[DataProvider('operationProvider')]
    public function testOperationMatchesJavaScript(string $operation): void
    {
        $mismatches = [];
        $count = 0;
        foreach (self::cases() as [$op, $args, $expected]) {
            if ($op !== $operation) {
                continue;
            }
            $count++;
            $actual = self::evaluate($op, $args);
            if ($actual !== $expected) {
                $mismatches[] = json_encode(['args' => $args, 'expected' => $expected, 'actual' => $actual]);
                if (count($mismatches) >= 10) {
                    break;
                }
            }
        }
        $this->assertGreaterThan(0, $count);
        $this->assertSame([], $mismatches, "$operation differs from JavaScript");
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function operationProvider(): array
    {
        $operations = [];
        foreach (self::cases() as [$op]) {
            $operations[$op] = [$op];
        }
        return $operations;
    }

    /**
     * @return list<array{string, list<mixed>, mixed}>
     */
    private static function cases(): array
    {
        if (self::$cases === null) {
            $files = glob(__DIR__ . '/fixtures/units/*.json') ?: [];
            if ($files === []) {
                throw new \RuntimeException('No golden files in fixtures/units');
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
            self::$cases = $cases;
        }
        return self::$cases;
    }

    /**
     * @param list<mixed> $args
     */
    private static function evaluate(string $op, array $args): mixed
    {
        try {
            // A plain loop: array_map with a first-class callable here
            // crashes PHP 8.3.6 when a decoded argument throws.
            $decoded = [];
            foreach ($args as $arg) {
                $decoded[] = self::decode($arg);
            }
            return self::encode(self::call($op, $decoded));
        } catch (\InvalidArgumentException | \UnexpectedValueException | \LogicException) {
            // The ports throw these where JavaScript throws an Error.
            return ['error' => true];
        }
    }

    /**
     * @param list<mixed> $a
     */
    private static function call(string $op, array $a): mixed
    {
        if (str_starts_with($op, 'comparison.')) {
            $method = substr($op, strlen('comparison.'));
            return Comparison::{$method}($a[0], $a[1]);
        }
        return match ($op) {
            'unit' => self::specifiedUnit(self::nullableString($a[0])),
            'convertUnitTo' => Ucum::instance()->convert(self::string($a[0]), self::number($a[1]), self::string($a[2])),
            'convertUnit' => Units::convertUnit(self::number($a[0]), self::nullableString($a[1]), self::nullableString($a[2])),
            'checkUnit' => Units::checkUnit(self::nullableString($a[0])),
            'convertToCQLDateUnit' => Units::convertToCqlDateUnit(self::nullableString($a[0])),
            'normalizeUnitsWhenPossible' => Units::normalizeUnitsWhenPossible(
                self::number($a[0]),
                self::nullableString($a[1]),
                self::number($a[2]),
                self::nullableString($a[3]),
            ),
            'compareUnits' => Units::compareUnits(self::nullableString($a[0]), self::nullableString($a[1])),
            'getProductOfUnits' => Units::getProductOfUnits(self::nullableString($a[0]), self::nullableString($a[1])),
            'getQuotientOfUnits' => Units::getQuotientOfUnits(self::nullableString($a[0]), self::nullableString($a[1])),
            'decimalAdjust' => CqlMath::decimalAdjust(self::number($a[0]), -8),
            'numberToString' => JavaScript::numberToString(self::number($a[0])),
            'overflowsOrUnderflows' => CqlMath::overflowsOrUnderflows($a[0]),
            'new' => new Quantity(self::number($a[0]), self::nullableString($a[1])),
            'parse' => Quantity::parse(self::string($a[0])),
            'dividedBy' => self::quantity($a[0])->dividedBy(self::quantityOrNumber($a[1])),
            'multiplyBy' => self::quantity($a[0])->multiplyBy(self::quantityOrNumber($a[1])),
            'add' => Quantity::doAddition(self::quantityOrTemporal($a[0]), $a[1]),
            'subtract' => Quantity::doSubtraction(self::quantityOrTemporal($a[0]), $a[1]),
            'quantityConvertUnit' => self::quantity($a[0])->convertUnit(self::nullableString($a[1] ?? null)),
            'toString' => self::quantity($a[0])->toString(),
            'ratioToString' => $a[0] instanceof Ratio ? $a[0]->toString() : throw new \UnexpectedValueException('Not a ratio'),
            default => throw new \UnexpectedValueException("Unknown operation $op"),
        };
    }

    /**
     * @return array{bool, ?UcumUnit} whether the unit is valid as written, and the unit
     */
    private static function specifiedUnit(?string $unit): array
    {
        if ($unit === null) {
            return [false, null];
        }
        [$parsed, $valid] = Ucum::instance()->specifiedUnit($unit);
        return [$valid, $parsed];
    }

    /** Rebuilds numbers, quantities, ratios and dates; anything else is as written. */
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
        if (is_array($value) && array_key_exists('q', $value) && is_array($value['q'])) {
            return new Quantity(self::number(self::decode($value['q'][0] ?? null)), self::nullableString($value['q'][1] ?? null));
        }
        if (is_array($value) && array_key_exists('r', $value) && is_array($value['r'])) {
            return new Ratio(self::quantity(self::decode($value['r'][0] ?? null)), self::quantity(self::decode($value['r'][1] ?? null)));
        }
        return $value;
    }

    private static function encode(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return '#' . JavaScript::numberToString($value);
        }
        if (is_array($value)) {
            return array_map(self::encode(...), $value);
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
            $offsetText = $offset === null ? '' : ($offset == floor($offset) ? (string) (int) $offset : (string) $offset);
            return implode('|', ['dt', ...array_map(static fn (?int $f): string => (string) $f, $fields), $offsetText]);
        }
        if ($value instanceof CqlDate) {
            return implode('|', ['d', (string) $value->year, (string) $value->month, (string) $value->day]);
        }
        return $value;
    }

    private static function quantity(mixed $value): Quantity
    {
        return $value instanceof Quantity ? $value : throw new \UnexpectedValueException('Not a quantity');
    }

    private static function quantityOrNumber(mixed $value): Quantity|int|float|null
    {
        return $value === null || is_int($value) || is_float($value) || $value instanceof Quantity
            ? $value
            : throw new \UnexpectedValueException('Not a quantity or number');
    }

    private static function quantityOrTemporal(mixed $value): Quantity|CqlTemporal
    {
        return $value instanceof Quantity || $value instanceof CqlTemporal
            ? $value
            : throw new \UnexpectedValueException('Not a quantity or date');
    }

    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : throw new \UnexpectedValueException('Not a string');
    }

    private static function nullableString(mixed $value): ?string
    {
        return $value === null ? null : self::string($value);
    }

    private static function number(mixed $value): float
    {
        return is_int($value) || is_float($value) ? (float) $value : throw new \UnexpectedValueException('Not a number');
    }
}
