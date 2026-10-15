<?php

/**
 * Replays results recorded from cql-execution (see golden.js) against the
 * PHP CQL types: DateTime and Date ordering, equality, arithmetic,
 * differences and durations, offset conversion, parsing, uncertainties and
 * generic comparison.
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
use OpenEMR\Cqm\Cql\Types\Precision;
use OpenEMR\Cqm\Cql\Types\Uncertainty;
use PHPUnit\Framework\TestCase;

class TemporalGoldenTest extends TestCase
{
    /** @var list<array{string, list<mixed>, mixed}>|null */
    private static ?array $cases = null;

    /**
     * One test per operation, so a failure names the operation; each
     * reports every mismatching case up to a limit.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('operationProvider')]
    public function testOperationMatchesCqlExecution(string $operation): void
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
        $this->assertSame([], $mismatches, "$operation differs from cql-execution");
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
            $files = glob(__DIR__ . '/fixtures/temporal/*.json') ?: [];
            if ($files === []) {
                throw new \RuntimeException('No golden files in fixtures/temporal');
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
            return self::encode(self::call($op, $args));
        } catch (\LogicException | \RuntimeException) {
            // The CQL types throw these where cql-execution throws an Error.
            return ['error' => true];
        }
    }

    /**
     * @param list<mixed> $args
     */
    private static function call(string $op, array $args): mixed
    {
        if (str_starts_with($op, 'comparison.')) {
            $method = substr($op, strlen('comparison.'));
            return Comparison::{$method}($args[0], $args[1]);
        }
        if (str_starts_with($op, 'uncertainty.')) {
            $method = substr($op, strlen('uncertainty.'));
            $a = self::uncertainty($args[0]);
            return $method === 'isPoint' ? $a->isPoint() : $a->{$method}(self::uncertainty($args[1]));
        }
        return match ($op) {
            'parseDateTime' => CqlDateTime::parse(self::string($args[0])),
            'parseDate' => CqlDate::parse(self::string($args[0])),
            'fromQdmString' => CqlDateTime::fromQdmString(self::string($args[0])),
            'sameAs', 'sameOrBefore', 'sameOrAfter', 'before', 'after'
                => self::temporal($args[0])->{$op}(self::decode($args[1]), self::precision($args[2] ?? null)),
            'equals', 'equivalent' => self::temporal($args[0])->{$op}(self::decode($args[1])),
            'differenceBetween', 'durationBetween'
                => self::temporal($args[0])->{$op}(self::decode($args[1]), Precision::from(self::string($args[2]))),
            'add' => self::temporal($args[0])->add(self::number($args[1]), Precision::from(self::string($args[2]))),
            'convertToTimezoneOffset' => self::dateTime($args[0])->convertToTimezoneOffset(self::nullableNumber($args[1])),
            'successor' => self::temporal($args[0])->successor(),
            'predecessor' => self::temporal($args[0])->predecessor(),
            'toString' => self::temporal($args[0])->toString(),
            'getPrecision' => self::temporal($args[0])->getPrecision()?->value,
            'reducedPrecision' => self::temporal($args[0])->reducedPrecision(Precision::from(self::string($args[1]))),
            default => throw new \UnexpectedValueException("Unknown operation $op"),
        };
    }

    /** Rebuilds "dt|year|...|offset" and "d|year|month|day" values; anything else is as written. */
    private static function decode(mixed $value): mixed
    {
        if (!is_string($value) || preg_match('/^(dt|d)\|/', $value, $kind) !== 1) {
            return $value;
        }
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

    private static function encode(mixed $value): mixed
    {
        if ($value instanceof CqlDateTime) {
            $fields = [$value->year, $value->month, $value->day, $value->hour, $value->minute, $value->second, $value->millisecond];
            return implode('|', ['dt', ...array_map(strval(...), [...$fields, self::jsNumber($value->timezoneOffset)])]);
        }
        if ($value instanceof CqlDate) {
            return implode('|', ['d', ...array_map(strval(...), [$value->year, $value->month, $value->day])]);
        }
        if ($value instanceof Uncertainty) {
            return ['u' => [self::encode($value->low), self::encode($value->high)]];
        }
        return $value;
    }

    /** JSON from JavaScript writes whole numbers without a decimal point. */
    private static function jsNumber(?float $value): int|float|null
    {
        if ($value === null) {
            return null;
        }
        return $value == floor($value) ? (int) $value : $value;
    }

    private static function temporal(mixed $value): CqlTemporal
    {
        $decoded = self::decode($value);
        return $decoded instanceof CqlTemporal ? $decoded : throw new \UnexpectedValueException('Not a date');
    }

    private static function dateTime(mixed $value): CqlDateTime
    {
        $decoded = self::decode($value);
        return $decoded instanceof CqlDateTime ? $decoded : throw new \UnexpectedValueException('Not a DateTime');
    }

    private static function uncertainty(mixed $value): Uncertainty
    {
        return is_array($value) ? new Uncertainty($value[0] ?? null, $value[1] ?? null) : throw new \UnexpectedValueException('Not a range');
    }

    private static function precision(mixed $value): ?Precision
    {
        return is_string($value) ? Precision::from($value) : null;
    }

    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : throw new \UnexpectedValueException('Not a string');
    }

    private static function number(mixed $value): int|float
    {
        return is_int($value) || is_float($value) ? $value : throw new \UnexpectedValueException('Not a number');
    }

    private static function nullableNumber(mixed $value): ?float
    {
        return $value === null ? null : (float) self::number($value);
    }
}
