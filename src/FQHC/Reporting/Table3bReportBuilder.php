<?php

/**
 * Aggregates parsed per-patient records into a UDS Table 3B report.
 *
 * Pure and deterministic: one pass over the records, no database, clock, or
 * global state. Each patient contributes one tally to one race×ethnicity cell
 * (so the Line 8 total equals the patient count) and increments the Line 12
 * language count when applicable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class Table3bReportBuilder
{
    /**
     * @param iterable<Table3bPatientRecord> $records
     */
    public function build(iterable $records): Table3bReport
    {
        $matrix = $this->zeroedMatrix();
        $languageCount = 0;

        foreach ($records as $record) {
            $matrix[$record->race->value][$record->ethnicity->value]++;
            if ($record->bestServedInNonEnglishLanguage) {
                $languageCount++;
            }
        }

        return new Table3bReport($matrix, $languageCount);
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function zeroedMatrix(): array
    {
        $matrix = [];
        foreach (UdsRaceCategory::cases() as $race) {
            foreach (UdsEthnicityCategory::cases() as $ethnicity) {
                $matrix[$race->value][$ethnicity->value] = 0;
            }
        }

        return $matrix;
    }
}
