<?php

/**
 * Isolated tests for the UDS Table 3A aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\Table3aAgeBand;
use OpenEMR\FQHC\Reporting\Table3aPatientRecord;
use OpenEMR\FQHC\Reporting\Table3aReportBuilder;
use OpenEMR\FQHC\Reporting\UdsSex;
use PHPUnit\Framework\TestCase;

final class Table3aReportBuilderTest extends TestCase
{
    public function testEmptyInputProducesAllZeroReport(): void
    {
        $report = (new Table3aReportBuilder())->build([]);

        self::assertSame(0, $report->total());
        self::assertSame(0, $report->sexTotal(UdsSex::Male));
        self::assertSame(0, $report->count(Table3aAgeBand::fromAge(30), UdsSex::Female));
    }

    public function testCountsByAgeBandAndSex(): void
    {
        $report = (new Table3aReportBuilder())->build([
            $this->record(0, UdsSex::Male),
            $this->record(0, UdsSex::Female),
            $this->record(0, UdsSex::Female),
            $this->record(40, UdsSex::Male),
            $this->record(85, UdsSex::Female),
        ]);

        self::assertSame(1, $report->count(Table3aAgeBand::fromAge(0), UdsSex::Male));
        self::assertSame(2, $report->count(Table3aAgeBand::fromAge(0), UdsSex::Female));
        self::assertSame(1, $report->count(Table3aAgeBand::fromAge(42), UdsSex::Male)); // same band as 40
        self::assertSame(1, $report->count(Table3aAgeBand::fromAge(90), UdsSex::Female)); // same band as 85
    }

    public function testSexTotalsAndGrandTotal(): void
    {
        $report = (new Table3aReportBuilder())->build([
            $this->record(10, UdsSex::Male),
            $this->record(20, UdsSex::Male),
            $this->record(33, UdsSex::Female),
        ]);

        self::assertSame(2, $report->sexTotal(UdsSex::Male));
        self::assertSame(1, $report->sexTotal(UdsSex::Female));
        self::assertSame(3, $report->total());
    }

    public function testTotalAlwaysEqualsPatientCount(): void
    {
        $records = [];
        foreach ([0, 1, 17, 18, 24, 25, 64, 65, 84, 85, 99] as $age) {
            $records[] = $this->record($age, UdsSex::Male);
        }

        self::assertSame(count($records), (new Table3aReportBuilder())->build($records)->total());
    }

    private function record(int $age, UdsSex $sex): Table3aPatientRecord
    {
        return new Table3aPatientRecord(Table3aAgeBand::fromAge($age), $sex);
    }
}
