<?php

/**
 * Aggregates parsed per-patient records into a UDS Table 3A report.
 *
 * Pure and deterministic: one pass over the records, no database, clock, or
 * global state. Every patient contributes exactly one tally in exactly one
 * age-band/sex cell, so the Line 39 total always equals the patient count.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class Table3aReportBuilder
{
    /**
     * @param iterable<Table3aPatientRecord> $records
     */
    public function build(iterable $records): Table3aReport
    {
        $counts = $this->zeroedCounts();

        foreach ($records as $record) {
            $counts[$record->ageBand->line][$record->sex->name]++;
        }

        return new Table3aReport($counts);
    }

    /**
     * @return array<int, array<string, int>>
     */
    private function zeroedCounts(): array
    {
        $counts = [];
        for ($line = Table3aAgeBand::FIRST_LINE; $line <= Table3aAgeBand::LAST_LINE; $line++) {
            foreach (UdsSex::cases() as $sex) {
                $counts[$line][$sex->name] = 0;
            }
        }

        return $counts;
    }
}
