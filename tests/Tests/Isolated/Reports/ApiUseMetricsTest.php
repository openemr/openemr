<?php

/**
 * Isolated ApiUseMetrics Test
 *
 * The counts come from SUM() and COUNT() columns, so every value arrives as a
 * numeric string, and SUM() over no rows is NULL. The Real World Testing report
 * concatenates these into its output, so they have to be narrowed to int once.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Reports;

use OpenEMR\Reports\ApiUseMetrics;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiUseMetricsTest extends TestCase
{
    public function testReadsTheCountsTheDriverReturnsAsStrings(): void
    {
        $metrics = ApiUseMetrics::fromRows(
            [
                'success_count' => '42',
                'fail_count' => '7',
                'user_count' => '30',
                'patient_count' => '12',
            ],
            []
        );

        self::assertSame(42, $metrics->successful);
        self::assertSame(7, $metrics->unsuccessful);
        self::assertSame(30, $metrics->byUsers);
        self::assertSame(12, $metrics->byPatients);
        self::assertSame([], $metrics->byResource);
    }

    public function testSumOverNoRowsIsNullAndCountsAsZero(): void
    {
        // An install with no API traffic in the period: the row exists, every
        // SUM() in it is NULL.
        $metrics = ApiUseMetrics::fromRows(
            [
                'success_count' => null,
                'fail_count' => null,
                'user_count' => null,
                'patient_count' => null,
            ],
            []
        );

        self::assertSame(0, $metrics->successful);
        self::assertSame(0, $metrics->unsuccessful);
        self::assertSame(0, $metrics->byUsers);
        self::assertSame(0, $metrics->byPatients);
    }

    public function testNoTotalsRowAtAllGivesZeros(): void
    {
        foreach ([false, null, []] as $totals) {
            $metrics = ApiUseMetrics::fromRows($totals, []);
            self::assertSame(0, $metrics->successful);
            self::assertSame(0, $metrics->unsuccessful);
            self::assertSame(0, $metrics->byUsers);
            self::assertSame(0, $metrics->byPatients);
        }
    }

    public function testBuildsTheResourceMapInTheOrderTheQueryReturns(): void
    {
        $metrics = ApiUseMetrics::fromRows(
            ['success_count' => '6'],
            [
                ['resource' => 'AllergyIntolerance', 'request_count' => '2'],
                ['resource' => 'Observation', 'request_count' => '3'],
                ['resource' => 'Patient', 'request_count' => '1'],
            ]
        );

        self::assertSame(
            ['AllergyIntolerance' => 2, 'Observation' => 3, 'Patient' => 1],
            $metrics->byResource
        );
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableResourceRowProvider(): array
    {
        return [
            'no resource key' => [['request_count' => '3']],
            'null resource' => [['resource' => null, 'request_count' => '3']],
            'empty resource' => [['resource' => '', 'request_count' => '3']],
            'resource is not a string' => [['resource' => 7, 'request_count' => '3']],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    #[DataProvider('unusableResourceRowProvider')]
    public function testSkipsAResourceRowItCannotName(array $row): void
    {
        $metrics = ApiUseMetrics::fromRows(['success_count' => '3'], [$row]);
        self::assertSame([], $metrics->byResource);
        // The totals still come through.
        self::assertSame(3, $metrics->successful);
    }

    public function testSkipsARowThatIsNotAnArray(): void
    {
        $metrics = ApiUseMetrics::fromRows([], ['not a row', null, 7]);
        self::assertSame([], $metrics->byResource);
    }

    /**
     * @return array<string, array{mixed, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function countValueProvider(): array
    {
        return [
            'a numeric string' => ['15', 15],
            'an int' => [15, 15],
            'zero as a string' => ['0', 0],
            'zero as an int' => [0, 0],
            'null' => [null, 0],
            'empty string' => ['', 0],
            // ctype_digit rejects these, and a count can be neither.
            'a negative string' => ['-1', 0],
            'a negative int' => [-1, 0],
            'a decimal string' => ['1.5', 0],
            'not a number' => ['many', 0],
            'a bool' => [true, 0],
            'an array' => [[1], 0],
        ];
    }

    #[DataProvider('countValueProvider')]
    public function testNarrowsACountValue(mixed $value, int $expected): void
    {
        $metrics = ApiUseMetrics::fromRows(
            ['success_count' => $value],
            [['resource' => 'Patient', 'request_count' => $value]]
        );
        self::assertSame($expected, $metrics->successful);
        self::assertSame($expected, $metrics->byResource['Patient']);
    }

    public function testAResourceCountIsKeptEvenWhenItNarrowsToZero(): void
    {
        // GROUP BY cannot produce a zero count, but the key must still appear
        // rather than vanish, so a malformed count is visible in the report.
        $metrics = ApiUseMetrics::fromRows([], [['resource' => 'Patient', 'request_count' => 'x']]);
        self::assertArrayHasKey('Patient', $metrics->byResource);
        self::assertSame(0, $metrics->byResource['Patient']);
    }
}
