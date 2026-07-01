<?php

/**
 * Aggregates countable visits into a UDS Table 5 utilization report.
 *
 * Pure and deterministic: one pass over the visits, no database, clock, or
 * global state. Visits tally into Column B or B2 by the virtual flag; patients
 * are collected into a per-category set so Column C is unduplicated within each
 * category while a patient seen for two categories is counted in both (the
 * manual's core Table 5 counting rule).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class Table5ReportBuilder
{
    /**
     * @param iterable<Table5VisitRecord> $visits
     */
    public function build(iterable $visits): Table5Report
    {
        $clinicVisits = $this->zeroedCounts();
        $virtualVisits = $this->zeroedCounts();
        /** @var array<string, array<int, true>> $patientSets */
        $patientSets = [];
        foreach (UdsServiceCategory::cases() as $category) {
            $patientSets[$category->value] = [];
        }

        foreach ($visits as $visit) {
            $key = $visit->category->value;
            if ($visit->virtual) {
                $virtualVisits[$key]++;
            } else {
                $clinicVisits[$key]++;
            }
            $patientSets[$key][$visit->pid] = true;
        }

        $patients = [];
        foreach ($patientSets as $key => $pids) {
            $patients[$key] = count($pids);
        }

        return new Table5Report($clinicVisits, $virtualVisits, $patients);
    }

    /**
     * @return array<string, int>
     */
    private function zeroedCounts(): array
    {
        $counts = [];
        foreach (UdsServiceCategory::cases() as $category) {
            $counts[$category->value] = 0;
        }

        return $counts;
    }
}
