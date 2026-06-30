<?php

/**
 * Aggregates parsed per-patient records into a UDS Table 4 report.
 *
 * Pure and deterministic: one pass over the records, no database, clock, or
 * global state. Encodes the UDS counting rules the manual requires:
 * unclassified insurance is counted as None/Uninsured (no "unknown" line);
 * Unknown income stays its own band; and special-population totals count
 * distinct patients while the breakout lines count each held subtype, so a
 * patient with several statuses is unduplicated in the totals.
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
use OpenEMR\FQHC\SpecialPopulation\AgriculturalWorkerType;
use OpenEMR\FQHC\SpecialPopulation\HomelessStatus;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;

final class Table4ReportBuilder
{
    /**
     * @param iterable<Table4PatientRecord> $records
     */
    public function build(iterable $records): Table4Report
    {
        $incomeCounts = $this->zeroedIncomeCounts();
        $insuranceCounts = $this->zeroedInsuranceCounts();
        $homelessCounts = $this->zeroedHomelessCounts();

        $migratory = 0;
        $seasonal = 0;
        $agriculturalTotal = 0;
        $homelessTotal = 0;
        $schoolBased = 0;
        $veterans = 0;
        $publicHousing = 0;
        $totalPatients = 0;

        foreach ($records as $record) {
            $totalPatients++;

            $incomeCounts[$record->incomeBand->name]++;

            $category = $record->payerCategory ?? UdsPayerCategory::None;
            $insuranceCounts[$category->value][$record->ageGroup->name]++;

            $populations = $this->populationsHeld($record);
            $homelessSubtypes = $this->homelessSubtypesHeld($record);
            $agriculturalSubtypes = $this->agriculturalSubtypesHeld($record);

            foreach ($homelessSubtypes as $value => $_held) {
                $homelessCounts[$value]++;
            }
            if (isset($populations[SpecialPopulation::Homeless->value])) {
                $homelessTotal++;
            }

            if (isset($agriculturalSubtypes[AgriculturalWorkerType::Migratory->value])) {
                $migratory++;
            }
            if (isset($agriculturalSubtypes[AgriculturalWorkerType::Seasonal->value])) {
                $seasonal++;
            }
            if (isset($populations[SpecialPopulation::AgriculturalWorker->value])) {
                $agriculturalTotal++;
            }

            if (isset($populations[SpecialPopulation::SchoolBased->value])) {
                $schoolBased++;
            }
            if (isset($populations[SpecialPopulation::Veteran->value])) {
                $veterans++;
            }
            if (isset($populations[SpecialPopulation::PublicHousing->value])) {
                $publicHousing++;
            }
        }

        return new Table4Report(
            new Table4IncomeSection($incomeCounts),
            new Table4InsuranceSection($insuranceCounts),
            new Table4SpecialPopulationsSection(
                migratoryAgriculturalWorkers: $migratory,
                seasonalAgriculturalWorkers: $seasonal,
                totalAgriculturalWorkers: $agriculturalTotal,
                homelessByType: $homelessCounts,
                totalHomeless: $homelessTotal,
                schoolBased: $schoolBased,
                veterans: $veterans,
                publicHousing: $publicHousing,
            ),
            $totalPatients,
        );
    }

    /**
     * @return array<string, int>
     */
    private function zeroedIncomeCounts(): array
    {
        $counts = [];
        foreach (FplBand::cases() as $band) {
            $counts[$band->name] = 0;
        }

        return $counts;
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function zeroedInsuranceCounts(): array
    {
        $counts = [];
        foreach (UdsPayerCategory::cases() as $category) {
            foreach (UdsAgeGroup::cases() as $ageGroup) {
                $counts[$category->value][$ageGroup->name] = 0;
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function zeroedHomelessCounts(): array
    {
        $counts = [];
        foreach (HomelessStatus::cases() as $status) {
            $counts[$status->value] = 0;
        }

        return $counts;
    }

    /**
     * Distinct populations the patient held, keyed by population value.
     *
     * @return array<string, true>
     */
    private function populationsHeld(Table4PatientRecord $record): array
    {
        $held = [];
        foreach ($record->specialPopulationStatuses as $status) {
            $held[$status->population->value] = true;
        }

        return $held;
    }

    /**
     * Distinct homeless housing subtypes held, keyed by HomelessStatus value.
     *
     * @return array<string, true>
     */
    private function homelessSubtypesHeld(Table4PatientRecord $record): array
    {
        $held = [];
        foreach ($record->specialPopulationStatuses as $status) {
            if ($status->population !== SpecialPopulation::Homeless || $status->subtype === null) {
                continue;
            }
            $subtype = HomelessStatus::tryFrom($status->subtype);
            if ($subtype !== null) {
                $held[$subtype->value] = true;
            }
        }

        return $held;
    }

    /**
     * Distinct agricultural-worker subtypes held, keyed by
     * AgriculturalWorkerType value.
     *
     * @return array<string, true>
     */
    private function agriculturalSubtypesHeld(Table4PatientRecord $record): array
    {
        $held = [];
        foreach ($record->specialPopulationStatuses as $status) {
            if ($status->population !== SpecialPopulation::AgriculturalWorker || $status->subtype === null) {
                continue;
            }
            $subtype = AgriculturalWorkerType::tryFrom($status->subtype);
            if ($subtype !== null) {
                $held[$subtype->value] = true;
            }
        }

        return $held;
    }
}
