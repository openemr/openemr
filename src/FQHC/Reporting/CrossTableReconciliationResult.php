<?php

/**
 * The unduplicated-patient totals the UDS Manual requires to agree across
 * tables, and whether they do.
 *
 * Per the manual's cross-table considerations (and UDS-DATA-MODEL-VALIDATION.md
 * §6): Table 3A Line 39 = the ZIP Code Table total = Table 4 Line 6 (income) =
 * Table 4 Line 12 (insurance). This captures the totals compared so a mismatch
 * points straight at the disagreeing table during pre-submission data-quality
 * review. (Table 3B and the ZIP table join this check as those slices land.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class CrossTableReconciliationResult
{
    public function __construct(
        public int $table3aTotalPatients,
        public int $table4IncomeTotal,
        public int $table4InsuranceTotal,
    ) {
    }

    public function isConsistent(): bool
    {
        return $this->table3aTotalPatients === $this->table4IncomeTotal
            && $this->table3aTotalPatients === $this->table4InsuranceTotal;
    }
}
