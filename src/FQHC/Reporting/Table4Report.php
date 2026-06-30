<?php

/**
 * A computed UDS Table 4 ("Selected Patient Characteristics").
 *
 * Composes the three reported sections — income by poverty guideline, principal
 * insurance, and special populations — plus the unduplicated patient total. The
 * report is a pure value object: the same set of patient records always yields
 * the same numbers, with no database or clock involved, so it is fully
 * reproducible and auditable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table4Report
{
    public function __construct(
        public Table4IncomeSection $income,
        public Table4InsuranceSection $insurance,
        public Table4SpecialPopulationsSection $specialPopulations,
        public int $totalPatients,
    ) {
    }

    /**
     * Within-table integrity: the income total (Line 6) and the insurance total
     * (Line 12) must each equal the unduplicated patient total. The UDS Manual
     * asserts this equality (and extends it across Tables 3A/3B and the ZIP
     * table — UDS-DATA-MODEL-VALIDATION.md §6); it is a cheap data-quality guard
     * before submission.
     */
    public function isInternallyConsistent(): bool
    {
        return $this->income->total() === $this->totalPatients
            && $this->insurance->total() === $this->totalPatients;
    }
}
