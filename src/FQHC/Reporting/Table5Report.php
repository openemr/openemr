<?php

/**
 * A computed UDS Table 5 ("Staffing and Utilization"), visits and patients only.
 *
 * For each service category it exposes Column B (in-person/clinic visits),
 * Column B2 (virtual visits), their total, and Column C (patients unduplicated
 * *within* the category). Grand-total patients is the sum of the per-category
 * counts — patients are duplicated *across* categories on this table, unlike the
 * patient-characteristics tables. Column A (FTE) is not modelled here; it needs
 * personnel configuration outside the encounter data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table5Report
{
    /**
     * @param array<string, int> $clinicVisits  in-person visits keyed by category value
     * @param array<string, int> $virtualVisits virtual visits keyed by category value
     * @param array<string, int> $patients      within-category unduplicated patients keyed by category value
     */
    public function __construct(
        private array $clinicVisits,
        private array $virtualVisits,
        private array $patients,
    ) {
    }

    public function clinicVisits(UdsServiceCategory $category): int
    {
        return $this->clinicVisits[$category->value] ?? 0;
    }

    public function virtualVisits(UdsServiceCategory $category): int
    {
        return $this->virtualVisits[$category->value] ?? 0;
    }

    public function totalVisits(UdsServiceCategory $category): int
    {
        return $this->clinicVisits($category) + $this->virtualVisits($category);
    }

    public function patients(UdsServiceCategory $category): int
    {
        return $this->patients[$category->value] ?? 0;
    }

    public function totalClinicVisits(): int
    {
        return array_sum($this->clinicVisits);
    }

    public function totalVirtualVisits(): int
    {
        return array_sum($this->virtualVisits);
    }

    public function grandTotalVisits(): int
    {
        return $this->totalClinicVisits() + $this->totalVirtualVisits();
    }

    /**
     * Sum of the per-category patient counts. Patients are duplicated across
     * categories on Table 5, so this is not the unduplicated health-center total.
     */
    public function totalPatients(): int
    {
        return array_sum($this->patients);
    }
}
