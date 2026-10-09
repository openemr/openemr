<?php

/**
 * Replays results recorded from cql-execution (see golden-intervals.js)
 * against the PHP Interval type and the successor, predecessor and limit
 * helpers it closes bounds with.
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
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Precision;
use OpenEMR\Cqm\Cql\Util\CqlMath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IntervalGoldenTest extends TestCase
{
    use GoldenCodec;

    /** Interval methods taking another interval or a point, and a precision. */
    private const COMPARING = [
        'contains', 'properlyIncludes', 'includes', 'includedIn', 'overlaps', 'overlapsAfter', 'overlapsBefore',
        'sameAs', 'sameOrBefore', 'sameOrAfter', 'after', 'before', 'meets', 'meetsAfter', 'meetsBefore',
        'starts', 'ends',
    ];

    /** @var list<array{string, list<mixed>, mixed}>|null */
    private static ?array $cases = null;

    /**
     * One test per operation, so a failure names the operation; each
     * reports every mismatching case up to a limit.
     */
    #[DataProvider('operationProvider')]
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
        return self::$cases ??= self::loadGoldenCases(__DIR__ . '/fixtures/intervals');
    }

    /**
     * @param list<mixed> $args
     */
    private static function evaluate(string $op, array $args): mixed
    {
        try {
            return self::encode(self::call($op, self::decodeAll($args)));
        } catch (\LogicException | \RuntimeException) {
            // The port throws these where cql-execution throws an Error.
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
        if (in_array($op, self::COMPARING, true)) {
            $precision = $a[2] ?? null;
            return self::interval($a[0])->{$op}($a[1], is_string($precision) ? Precision::from($precision) : null);
        }
        return match ($op) {
            'union', 'intersect', 'except', 'equals' => self::interval($a[0])->{$op}($a[1]),
            'start', 'end', 'toClosed', 'width', 'size', 'getPointSize', 'toString', 'copy', 'pointType'
                => self::interval($a[0])->{$op}(),
            'successor', 'predecessor', 'maxValueForInstance', 'minValueForInstance' => CqlMath::{$op}($a[0]),
            'maxValueForType', 'minValueForType' => CqlMath::{$op}(
                self::goldenNullableString($a[0]),
                ($a[1] ?? null) === null ? null : self::goldenQuantity($a[1]),
            ),
            default => throw new \UnexpectedValueException("Unknown operation $op"),
        };
    }

    private static function interval(mixed $value): Interval
    {
        return $value instanceof Interval ? $value : throw new \UnexpectedValueException('Not an interval');
    }
}
