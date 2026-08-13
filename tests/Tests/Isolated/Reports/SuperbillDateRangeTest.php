<?php

/**
 * Tests the inclusive date range used by the Visits -> Superbill report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Reports;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SuperbillDateRangeTest extends TestCase
{
    private string $reportSource;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 4) . '/interface/reports/custom_report_range.php';
        $source = file_get_contents($path);
        self::assertNotFalse($source, "Could not read {$path}");
        $this->reportSource = $source;
    }

    #[Test]
    public function productionQueryUsesAnIndexFriendlyInclusiveEndDate(): void
    {
        self::assertMatchesRegularExpression(
            '/date\s*>=\s*\?\s+and\s+date\s*<\s*DATE_ADD\s*\(\s*\?\s*,\s*INTERVAL\s+1\s+DAY\s*\)/i',
            $this->reportSource
        );
        self::assertDoesNotMatchRegularExpression('/DATE\s*\(\s*date\s*\)/i', $this->reportSource);
        self::assertStringContainsString(
            'array_push($sqlBindArray, $startdate, $enddate);',
            $this->reportSource
        );
    }

    /**
     * These examples independently exercise the boundary represented by the
     * production SQL: [start date, day after end date).
     *
     * @return iterable<string, array{string, string, string, bool}>
     */
    public static function dateRangeCases(): iterable
    {
        yield 'same-day afternoon is included' => [
            '2026-08-13',
            '2026-08-13',
            '2026-08-13 15:30:00',
            true,
        ];
        yield 'midnight of the following day is excluded' => [
            '2026-08-13',
            '2026-08-13',
            '2026-08-14 00:00:00',
            false,
        ];
        yield 'afternoon of a multi-day end date is included' => [
            '2026-08-11',
            '2026-08-13',
            '2026-08-13 23:59:59',
            true,
        ];
    }

    #[Test]
    #[DataProvider('dateRangeCases')]
    public function halfOpenBoundsPreserveInclusiveDateSemantics(
        string $startDate,
        string $endDate,
        string $formDate,
        bool $expected
    ): void {
        $start = new DateTimeImmutable($startDate);
        $exclusiveEnd = (new DateTimeImmutable($endDate))->modify('+1 day');
        $formDateTime = new DateTimeImmutable($formDate);

        self::assertSame($expected, $formDateTime >= $start && $formDateTime < $exclusiveEnd);
    }
}
