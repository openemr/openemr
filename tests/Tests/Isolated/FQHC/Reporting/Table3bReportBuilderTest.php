<?php

/**
 * Isolated tests for the UDS Table 3B aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\Table3bPatientRecord;
use OpenEMR\FQHC\Reporting\Table3bReportBuilder;
use OpenEMR\FQHC\Reporting\UdsEthnicityCategory;
use OpenEMR\FQHC\Reporting\UdsRaceCategory;
use PHPUnit\Framework\TestCase;

final class Table3bReportBuilderTest extends TestCase
{
    public function testEmptyInputProducesZeroReport(): void
    {
        $report = (new Table3bReportBuilder())->build([]);

        self::assertSame(0, $report->totalPatients());
        self::assertSame(0, $report->totalHispanic());
        self::assertSame(0, $report->patientsBestServedInNonEnglishLanguage);
    }

    public function testAsianSubCategoriesRollUpToLineOne(): void
    {
        $report = (new Table3bReportBuilder())->build([
            $this->record(UdsRaceCategory::Chinese, UdsEthnicityCategory::NotHispanic),
            $this->record(UdsRaceCategory::Vietnamese, UdsEthnicityCategory::NotHispanic),
            $this->record(UdsRaceCategory::AsianIndian, UdsEthnicityCategory::NotHispanic),
        ]);

        self::assertSame(3, $report->raceLineTotal(1));
        self::assertSame(1, $report->raceTotal(UdsRaceCategory::Chinese));
        self::assertSame(0, $report->raceLineTotal(2));
    }

    public function testNativeHawaiianPacificIslanderRollUpToLineTwo(): void
    {
        $report = (new Table3bReportBuilder())->build([
            $this->record(UdsRaceCategory::NativeHawaiian, UdsEthnicityCategory::NotHispanic),
            $this->record(UdsRaceCategory::Samoan, UdsEthnicityCategory::NotHispanic),
        ]);

        self::assertSame(2, $report->raceLineTotal(2));
    }

    public function testTotalHispanicSumsTheFiveHispanicColumns(): void
    {
        $report = (new Table3bReportBuilder())->build([
            $this->record(UdsRaceCategory::White, UdsEthnicityCategory::Mexican),
            $this->record(UdsRaceCategory::White, UdsEthnicityCategory::PuertoRican),
            $this->record(UdsRaceCategory::BlackOrAfricanAmerican, UdsEthnicityCategory::Cuban),
            $this->record(UdsRaceCategory::White, UdsEthnicityCategory::NotHispanic),
            $this->record(UdsRaceCategory::White, UdsEthnicityCategory::Unreported),
        ]);

        self::assertSame(3, $report->totalHispanic());
        self::assertSame(1, $report->ethnicityTotal(UdsEthnicityCategory::NotHispanic));
        self::assertSame(1, $report->ethnicityTotal(UdsEthnicityCategory::Unreported));
        // Column d total = a (3) + b (1) + c (1)
        self::assertSame(5, $report->totalPatients());
    }

    public function testRaceLineCountIsScopedToOneEthnicityColumn(): void
    {
        $report = (new Table3bReportBuilder())->build([
            $this->record(UdsRaceCategory::Chinese, UdsEthnicityCategory::Mexican),
            $this->record(UdsRaceCategory::Korean, UdsEthnicityCategory::NotHispanic),
        ]);

        self::assertSame(1, $report->raceLineCount(1, UdsEthnicityCategory::Mexican));
        self::assertSame(1, $report->raceLineCount(1, UdsEthnicityCategory::NotHispanic));
        self::assertSame(0, $report->raceLineCount(1, UdsEthnicityCategory::Cuban));
    }

    public function testLanguageLineCountsOnlyFlaggedPatients(): void
    {
        $report = (new Table3bReportBuilder())->build([
            new Table3bPatientRecord(UdsRaceCategory::White, UdsEthnicityCategory::NotHispanic, true),
            new Table3bPatientRecord(UdsRaceCategory::White, UdsEthnicityCategory::NotHispanic, false),
            new Table3bPatientRecord(UdsRaceCategory::White, UdsEthnicityCategory::NotHispanic, true),
        ]);

        self::assertSame(2, $report->patientsBestServedInNonEnglishLanguage);
        self::assertSame(3, $report->totalPatients());
    }

    public function testLine8TotalEqualsPatientCountAcrossAllCategories(): void
    {
        $report = (new Table3bReportBuilder())->build([
            $this->record(UdsRaceCategory::Unreported, UdsEthnicityCategory::Unreported),
            $this->record(UdsRaceCategory::MoreThanOneRace, UdsEthnicityCategory::Combined),
            $this->record(UdsRaceCategory::AmericanIndianAlaskaNative, UdsEthnicityCategory::Another),
        ]);

        $lineTotals = 0;
        foreach (range(1, 7) as $line) {
            $lineTotals += $report->raceLineTotal($line);
        }

        self::assertSame(3, $report->totalPatients());
        self::assertSame(3, $lineTotals, 'sum of race Lines 1-7 equals Line 8');
    }

    private function record(UdsRaceCategory $race, UdsEthnicityCategory $ethnicity): Table3bPatientRecord
    {
        return new Table3bPatientRecord($race, $ethnicity, false);
    }
}
