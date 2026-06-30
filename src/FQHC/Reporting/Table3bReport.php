<?php

/**
 * A computed UDS Table 3B ("Demographic Characteristics").
 *
 * Holds the race × Hispanic-ethnicity matrix at detailed granularity and
 * exposes the manual's roll-ups: the Asian (Line 1) and NHOPI (Line 2) totals,
 * the per-line race counts, the Total Hispanic column (a = a1…a5), each single
 * ethnicity column, the Line 8 grand total, and the Line 12 count of patients
 * best served in a language other than English. A pure value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table3bReport
{
    /**
     * @param array<string, array<string, int>> $matrix UdsRaceCategory value =>
     *        (UdsEthnicityCategory value => count)
     */
    public function __construct(
        private array $matrix,
        public int $patientsBestServedInNonEnglishLanguage,
    ) {
    }

    /**
     * A single detailed matrix cell (e.g. Chinese × Puerto Rican).
     */
    public function count(UdsRaceCategory $race, UdsEthnicityCategory $ethnicity): int
    {
        return $this->matrix[$race->value][$ethnicity->value] ?? 0;
    }

    /**
     * Detailed race category total across every ethnicity column (its column d).
     */
    public function raceTotal(UdsRaceCategory $race): int
    {
        return array_sum($this->matrix[$race->value] ?? []);
    }

    /**
     * Rolled-up race line (1–7) count within a single ethnicity column — Lines 1
     * and 2 fold their Asian / NHOPI sub-categories together.
     */
    public function raceLineCount(int $line, UdsEthnicityCategory $ethnicity): int
    {
        $sum = 0;
        foreach (UdsRaceCategory::cases() as $race) {
            if ($race->rollupLine() === $line) {
                $sum += $this->matrix[$race->value][$ethnicity->value] ?? 0;
            }
        }

        return $sum;
    }

    /**
     * Rolled-up race line total across every ethnicity column (its column d).
     */
    public function raceLineTotal(int $line): int
    {
        $sum = 0;
        foreach (UdsRaceCategory::cases() as $race) {
            if ($race->rollupLine() === $line) {
                $sum += array_sum($this->matrix[$race->value] ?? []);
            }
        }

        return $sum;
    }

    /**
     * One ethnicity column's total across every race (e.g. all Not Hispanic).
     */
    public function ethnicityTotal(UdsEthnicityCategory $ethnicity): int
    {
        $sum = 0;
        foreach ($this->matrix as $byEthnicity) {
            $sum += $byEthnicity[$ethnicity->value] ?? 0;
        }

        return $sum;
    }

    /**
     * Total Hispanic column (a): the sum of the five Hispanic sub-columns.
     */
    public function totalHispanic(): int
    {
        $sum = 0;
        foreach (UdsEthnicityCategory::cases() as $ethnicity) {
            if ($ethnicity->isHispanic()) {
                $sum += $this->ethnicityTotal($ethnicity);
            }
        }

        return $sum;
    }

    /**
     * Total patients (Line 8 / column d).
     */
    public function totalPatients(): int
    {
        $sum = 0;
        foreach ($this->matrix as $byEthnicity) {
            $sum += array_sum($byEthnicity);
        }

        return $sum;
    }
}
