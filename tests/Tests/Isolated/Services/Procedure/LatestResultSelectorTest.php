<?php

/**
 * Isolated LatestResultSelector Test
 *
 * When an order's results are shown "latest only", a report read later
 * replaces the results kept for the same result code, unless both reports
 * have the same report date and the later one's results are older.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Procedure;

use OpenEMR\Services\Procedure\LatestResultSelector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LatestResultSelectorTest extends TestCase
{
    /**
     * @return array<string, array{?string, ?string, ?string, ?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function reportsProvider(): array
    {
        // kept report date, kept result date, new report date, new result date, keeps the earlier report
        return [
            'same report date, new results older' => ['2026-09-02 10:00:00', '2026-09-02 09:00:00', '2026-09-02 10:00:00', '2026-09-01 09:00:00', true],
            'same report date, new results newer' => ['2026-09-02 10:00:00', '2026-09-01 09:00:00', '2026-09-02 10:00:00', '2026-09-02 09:00:00', false],
            'same report date, same result date' => ['2026-09-02 10:00:00', '2026-09-02 09:00:00', '2026-09-02 10:00:00', '2026-09-02 09:00:00', false],
            'later report date, new results older' => ['2026-09-02 10:00:00', '2026-09-02 09:00:00', '2026-09-03 10:00:00', '2026-09-01 09:00:00', false],
            'same report date, new result undated' => ['2026-09-02 10:00:00', '2026-09-02 09:00:00', '2026-09-02 10:00:00', null, false],
            'same report date, kept result undated' => ['2026-09-02 10:00:00', null, '2026-09-02 10:00:00', '2026-09-01 09:00:00', false],
            'no report dates' => [null, '2026-09-02 09:00:00', null, '2026-09-01 09:00:00', false],
        ];
    }

    #[DataProvider('reportsProvider')]
    public function testKeepsEarlierReport(?string $keptReportDate, ?string $keptResultDate, ?string $reportDate, ?string $resultDate, bool $expected): void
    {
        self::assertSame($expected, LatestResultSelector::keepsEarlierReport(
            ['date_report' => $keptReportDate],
            [['result' => '7.0', 'date' => $keptResultDate]],
            ['date_report' => $reportDate],
            [['result' => '5.0', 'date' => $resultDate]],
        ));
    }

    public function testComparesTheFirstResultOfEachReport(): void
    {
        self::assertTrue(LatestResultSelector::keepsEarlierReport(
            ['date_report' => '2026-09-02 10:00:00'],
            [['date' => '2026-09-02 09:00:00'], ['date' => '2026-09-02 09:05:00']],
            ['date_report' => '2026-09-02 10:00:00'],
            [['date' => '2026-09-01 09:00:00'], ['date' => '2026-09-03 09:00:00']],
        ));
    }

    public function testEmptyResultListsDoNotKeepTheEarlierReport(): void
    {
        self::assertFalse(LatestResultSelector::keepsEarlierReport(
            ['date_report' => '2026-09-02 10:00:00'],
            [],
            ['date_report' => '2026-09-02 10:00:00'],
            [],
        ));
    }
}
