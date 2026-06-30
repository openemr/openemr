<?php

/**
 * Isolated tests for the UDS Table 4 aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\Reporting\Table4PatientRecord;
use OpenEMR\FQHC\Reporting\Table4ReportBuilder;
use OpenEMR\FQHC\Reporting\UdsAgeGroup;
use OpenEMR\FQHC\SpecialPopulation\HomelessStatus;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;
use PHPUnit\Framework\TestCase;

final class Table4ReportBuilderTest extends TestCase
{
    public function testEmptyInputProducesAllZeroReport(): void
    {
        $report = (new Table4ReportBuilder())->build([]);

        self::assertSame(0, $report->totalPatients);
        self::assertSame(0, $report->income->total());
        self::assertSame(0, $report->insurance->total());
        self::assertSame(0, $report->income->count(FplBand::Unknown));
        self::assertTrue($report->isInternallyConsistent());
    }

    public function testCountsIncomeBandsAndKeepsUnknownSeparate(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(income: FplBand::AtOrBelow100),
            $this->record(income: FplBand::AtOrBelow100),
            $this->record(income: FplBand::From151To200),
            $this->record(income: FplBand::Unknown),
        ]);

        self::assertSame(2, $report->income->count(FplBand::AtOrBelow100));
        self::assertSame(0, $report->income->count(FplBand::From101To150));
        self::assertSame(1, $report->income->count(FplBand::From151To200));
        self::assertSame(1, $report->income->count(FplBand::Unknown));
        self::assertSame(4, $report->income->total());
    }

    public function testInsuranceSplitsByAgeColumn(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(payer: UdsPayerCategory::Medicaid, ageGroup: UdsAgeGroup::Under18),
            $this->record(payer: UdsPayerCategory::Medicaid, ageGroup: UdsAgeGroup::Under18),
            $this->record(payer: UdsPayerCategory::Medicaid, ageGroup: UdsAgeGroup::EighteenAndOver),
            $this->record(payer: UdsPayerCategory::Private, ageGroup: UdsAgeGroup::EighteenAndOver),
        ]);

        self::assertSame(2, $report->insurance->count(UdsPayerCategory::Medicaid, UdsAgeGroup::Under18));
        self::assertSame(1, $report->insurance->count(UdsPayerCategory::Medicaid, UdsAgeGroup::EighteenAndOver));
        self::assertSame(3, $report->insurance->categoryTotal(UdsPayerCategory::Medicaid));
        self::assertSame(1, $report->insurance->categoryTotal(UdsPayerCategory::Private));
        self::assertSame(4, $report->insurance->total());
    }

    public function testUnclassifiedPayerIsCountedAsNoneUninsured(): void
    {
        // UDS Table 4 has no "unknown insurance" line: a null category must land
        // in None/Uninsured, not be dropped (UDS-DATA-MODEL-VALIDATION.md §4).
        $report = (new Table4ReportBuilder())->build([
            $this->record(payer: null, ageGroup: UdsAgeGroup::EighteenAndOver),
            $this->record(payer: null, ageGroup: UdsAgeGroup::Under18),
        ]);

        self::assertSame(2, $report->insurance->categoryTotal(UdsPayerCategory::None));
        self::assertSame(2, $report->insurance->total());
        self::assertSame(2, $report->totalPatients);
        self::assertTrue($report->isInternallyConsistent());
    }

    public function testHomelessBreakoutIncludesUnknownAndTotalsAreUnduplicated(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(specialPopulations: [$this->homeless(HomelessStatus::Shelter)]),
            $this->record(specialPopulations: [$this->homeless(HomelessStatus::Street)]),
            $this->record(specialPopulations: [$this->homeless(HomelessStatus::Unknown)]),
        ]);

        $section = $report->specialPopulations;
        self::assertSame(1, $section->homeless(HomelessStatus::Shelter));
        self::assertSame(1, $section->homeless(HomelessStatus::Street));
        self::assertSame(1, $section->homeless(HomelessStatus::Unknown));
        self::assertSame(0, $section->homeless(HomelessStatus::Transitional));
        self::assertSame(3, $section->totalHomeless);
    }

    public function testAgriculturalWorkerHeldAsBothSubtypesCountsOnceInTotal(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(specialPopulations: [
                new SpecialPopulationStatus(SpecialPopulation::AgriculturalWorker, 'migratory'),
                new SpecialPopulationStatus(SpecialPopulation::AgriculturalWorker, 'seasonal'),
            ]),
        ]);

        $section = $report->specialPopulations;
        self::assertSame(1, $section->migratoryAgriculturalWorkers);
        self::assertSame(1, $section->seasonalAgriculturalWorkers);
        self::assertSame(1, $section->totalAgriculturalWorkers, 'distinct-patient total is unduplicated');
    }

    public function testPatientCountedInEverySpecialPopulationHeld(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(specialPopulations: [
                $this->homeless(HomelessStatus::Shelter),
                new SpecialPopulationStatus(SpecialPopulation::Veteran),
                new SpecialPopulationStatus(SpecialPopulation::PublicHousing),
                new SpecialPopulationStatus(SpecialPopulation::SchoolBased),
            ]),
        ]);

        $section = $report->specialPopulations;
        self::assertSame(1, $section->totalHomeless);
        self::assertSame(1, $section->veterans);
        self::assertSame(1, $section->publicHousing);
        self::assertSame(1, $section->schoolBased);
        self::assertSame(1, $report->totalPatients);
    }

    public function testFullReportIsInternallyConsistentAndTotalsAgree(): void
    {
        $report = (new Table4ReportBuilder())->build([
            $this->record(income: FplBand::AtOrBelow100, payer: UdsPayerCategory::Medicaid, ageGroup: UdsAgeGroup::Under18),
            $this->record(income: FplBand::From101To150, payer: null, ageGroup: UdsAgeGroup::EighteenAndOver),
            $this->record(income: FplBand::Unknown, payer: UdsPayerCategory::Medicare, ageGroup: UdsAgeGroup::EighteenAndOver),
        ]);

        self::assertSame(3, $report->totalPatients);
        self::assertSame(3, $report->income->total());
        self::assertSame(3, $report->insurance->total());
        self::assertTrue($report->isInternallyConsistent());
    }

    /**
     * @param list<SpecialPopulationStatus> $specialPopulations
     */
    private function record(
        FplBand $income = FplBand::AtOrBelow100,
        ?UdsPayerCategory $payer = UdsPayerCategory::None,
        UdsAgeGroup $ageGroup = UdsAgeGroup::EighteenAndOver,
        array $specialPopulations = [],
    ): Table4PatientRecord {
        return new Table4PatientRecord($income, $payer, $ageGroup, $specialPopulations);
    }

    private function homeless(HomelessStatus $status): SpecialPopulationStatus
    {
        return new SpecialPopulationStatus(SpecialPopulation::Homeless, $status->value);
    }
}
