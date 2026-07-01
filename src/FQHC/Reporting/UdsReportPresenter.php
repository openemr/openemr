<?php

/**
 * Flattens a computed UDS patient-characteristics report into plain row arrays
 * for the report template.
 *
 * Pure presentation logic: it iterates the report's enums and accessors and
 * emits Twig-ready rows, so the template carries no logic and this mapping is
 * unit-testable without a database or a rendering engine.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\SpecialPopulation\HomelessStatus;

/**
 * @phpstan-type CountRow array{label: string, count: int}
 * @phpstan-type InsuranceRow array{label: string, age0to17: int, age18AndOver: int, total: int}
 * @phpstan-type AgeRow array{label: string, male: int, female: int, total: int}
 * @phpstan-type RaceRow array{label: string, detail: bool, hispanic: int, notHispanic: int, unreported: int, total: int}
 * @phpstan-type ZipRow array{label: string, uninsured: int, publicInsurance: int, medicare: int, private: int, total: int}
 * @phpstan-type Table5Row array{label: string, clinic: int, virtual: int, visits: int, patients: int}
 */
final class UdsReportPresenter
{
    /**
     * @return array{
     *     summary: array{year: int, cohortSize: int, consistent: bool, patientTotalsAgree: bool, zipColumnsAgree: bool},
     *     income: array{rows: list<CountRow>, total: int},
     *     insurance: array{rows: list<InsuranceRow>, total: int},
     *     specialPopulations: list<CountRow>,
     *     ageSex: array{rows: list<AgeRow>, male: int, female: int, total: int},
     *     race: array{rows: list<RaceRow>, hispanic: int, notHispanic: int, unreported: int, total: int, language: int},
     *     zip: array{rows: list<ZipRow>, uninsured: int, publicInsurance: int, medicare: int, private: int, total: int}
     * }
     */
    public function present(UdsPatientCharacteristicsReport $report): array
    {
        return [
            'summary' => [
                'year' => $report->year,
                'cohortSize' => $report->cohortSize,
                'consistent' => $report->reconciliation->isConsistent(),
                'patientTotalsAgree' => $report->reconciliation->patientTotalsAgree(),
                'zipColumnsAgree' => $report->reconciliation->zipColumnsAgreeWithTable4(),
            ],
            'income' => $this->income($report->table4),
            'insurance' => $this->insurance($report->table4),
            'specialPopulations' => $this->specialPopulations($report->table4),
            'ageSex' => $this->ageSex($report->table3a),
            'race' => $this->race($report->table3b),
            'zip' => $this->zip($report->zipCodeTable),
        ];
    }

    /**
     * The UDS Table 5 utilization rows (one per service category) plus totals.
     *
     * @return array{rows: list<Table5Row>, clinic: int, virtual: int, visits: int, patients: int}
     */
    public function table5(Table5Report $table5): array
    {
        $rows = [];
        foreach (UdsServiceCategory::cases() as $category) {
            $rows[] = [
                'label' => $category->label(),
                'clinic' => $table5->clinicVisits($category),
                'virtual' => $table5->virtualVisits($category),
                'visits' => $table5->totalVisits($category),
                'patients' => $table5->patients($category),
            ];
        }

        return [
            'rows' => $rows,
            'clinic' => $table5->totalClinicVisits(),
            'virtual' => $table5->totalVirtualVisits(),
            'visits' => $table5->grandTotalVisits(),
            'patients' => $table5->totalPatients(),
        ];
    }

    /**
     * @return array{rows: list<CountRow>, total: int}
     */
    private function income(Table4Report $table4): array
    {
        $rows = [];
        foreach (FplBand::cases() as $band) {
            $rows[] = ['label' => $band->label(), 'count' => $table4->income->count($band)];
        }

        return ['rows' => $rows, 'total' => $table4->income->total()];
    }

    /**
     * @return array{rows: list<InsuranceRow>, total: int}
     */
    private function insurance(Table4Report $table4): array
    {
        $section = $table4->insurance;
        $rows = [];
        foreach (UdsPayerCategory::cases() as $category) {
            $rows[] = [
                'label' => $category->label(),
                'age0to17' => $section->count($category, UdsAgeGroup::Under18),
                'age18AndOver' => $section->count($category, UdsAgeGroup::EighteenAndOver),
                'total' => $section->categoryTotal($category),
            ];
        }

        return ['rows' => $rows, 'total' => $section->total()];
    }

    /**
     * @return list<CountRow>
     */
    private function specialPopulations(Table4Report $table4): array
    {
        $section = $table4->specialPopulations;

        $rows = [
            ['label' => 'Migratory agricultural workers', 'count' => $section->migratoryAgriculturalWorkers],
            ['label' => 'Seasonal agricultural workers', 'count' => $section->seasonalAgriculturalWorkers],
            ['label' => 'Total agricultural workers', 'count' => $section->totalAgriculturalWorkers],
        ];
        foreach (HomelessStatus::cases() as $status) {
            $rows[] = ['label' => 'Homeless — ' . $status->label(), 'count' => $section->homeless($status)];
        }
        $rows[] = ['label' => 'Total homeless', 'count' => $section->totalHomeless];
        $rows[] = ['label' => 'School-based', 'count' => $section->schoolBased];
        $rows[] = ['label' => 'Veterans', 'count' => $section->veterans];
        $rows[] = ['label' => 'Public housing residents', 'count' => $section->publicHousing];

        return $rows;
    }

    /**
     * @return array{rows: list<AgeRow>, male: int, female: int, total: int}
     */
    private function ageSex(Table3aReport $table3a): array
    {
        $rows = [];
        for ($line = Table3aAgeBand::FIRST_LINE; $line <= Table3aAgeBand::LAST_LINE; $line++) {
            $band = new Table3aAgeBand($line);
            $male = $table3a->count($band, UdsSex::Male);
            $female = $table3a->count($band, UdsSex::Female);
            $rows[] = ['label' => $band->label(), 'male' => $male, 'female' => $female, 'total' => $male + $female];
        }

        return [
            'rows' => $rows,
            'male' => $table3a->sexTotal(UdsSex::Male),
            'female' => $table3a->sexTotal(UdsSex::Female),
            'total' => $table3a->total(),
        ];
    }

    /**
     * @return array{rows: list<RaceRow>, hispanic: int, notHispanic: int, unreported: int, total: int, language: int}
     */
    private function race(Table3bReport $table3b): array
    {
        $rows = [];
        foreach (UdsRaceCategory::cases() as $race) {
            $hispanic = 0;
            foreach (UdsEthnicityCategory::cases() as $ethnicity) {
                if ($ethnicity->isHispanic()) {
                    $hispanic += $table3b->count($race, $ethnicity);
                }
            }
            $rows[] = [
                'label' => $race->label(),
                'detail' => $race->rollupLine() === 1 || $race->rollupLine() === 2,
                'hispanic' => $hispanic,
                'notHispanic' => $table3b->count($race, UdsEthnicityCategory::NotHispanic),
                'unreported' => $table3b->count($race, UdsEthnicityCategory::Unreported),
                'total' => $table3b->raceTotal($race),
            ];
        }

        return [
            'rows' => $rows,
            'hispanic' => $table3b->totalHispanic(),
            'notHispanic' => $table3b->ethnicityTotal(UdsEthnicityCategory::NotHispanic),
            'unreported' => $table3b->ethnicityTotal(UdsEthnicityCategory::Unreported),
            'total' => $table3b->totalPatients(),
            'language' => $table3b->patientsBestServedInNonEnglishLanguage,
        ];
    }

    /**
     * @return array{rows: list<ZipRow>, uninsured: int, publicInsurance: int, medicare: int, private: int, total: int}
     */
    private function zip(ZipCodeTableReport $zipCodeTable): array
    {
        $rows = [];
        foreach ($zipCodeTable->residences() as $residence) {
            $rows[] = [
                'label' => $residence->label(),
                'uninsured' => $zipCodeTable->count($residence, UdsZipInsuranceColumn::Uninsured),
                'publicInsurance' => $zipCodeTable->count($residence, UdsZipInsuranceColumn::MedicaidChipOtherPublic),
                'medicare' => $zipCodeTable->count($residence, UdsZipInsuranceColumn::Medicare),
                'private' => $zipCodeTable->count($residence, UdsZipInsuranceColumn::Private),
                'total' => $zipCodeTable->residenceTotal($residence),
            ];
        }

        return [
            'rows' => $rows,
            'uninsured' => $zipCodeTable->columnTotal(UdsZipInsuranceColumn::Uninsured),
            'publicInsurance' => $zipCodeTable->columnTotal(UdsZipInsuranceColumn::MedicaidChipOtherPublic),
            'medicare' => $zipCodeTable->columnTotal(UdsZipInsuranceColumn::Medicare),
            'private' => $zipCodeTable->columnTotal(UdsZipInsuranceColumn::Private),
            'total' => $zipCodeTable->total(),
        ];
    }
}
