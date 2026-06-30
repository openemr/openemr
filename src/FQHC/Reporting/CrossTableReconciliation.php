<?php

/**
 * Checks the UDS unduplicated-patient-total equalities across the report tables.
 *
 * Pure and deterministic: it reads the already-computed table reports and
 * reports whether their patient totals agree (UDS-DATA-MODEL-VALIDATION.md §6).
 * It does not recount or mutate anything — it is a data-quality guard meant to
 * run before submission and on every report regeneration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class CrossTableReconciliation
{
    public function reconcile(Table3aReport $table3a, Table4Report $table4): CrossTableReconciliationResult
    {
        return new CrossTableReconciliationResult(
            table3aTotalPatients: $table3a->total(),
            table4IncomeTotal: $table4->income->total(),
            table4InsuranceTotal: $table4->insurance->total(),
        );
    }
}
