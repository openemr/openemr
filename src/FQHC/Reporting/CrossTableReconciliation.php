<?php

/**
 * Checks the UDS unduplicated-patient-total and ZIP insurance-column equalities
 * across the patient report tables.
 *
 * Pure and deterministic: it reads the already-computed table reports and
 * reports whether their totals agree (UDS-DATA-MODEL-VALIDATION.md §6). It does
 * not recount or mutate anything — it is a data-quality guard meant to run
 * before submission and on every report regeneration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\Payer\UdsPayerCategory;

final class CrossTableReconciliation
{
    public function reconcile(
        Table3aReport $table3a,
        Table3bReport $table3b,
        ZipCodeTableReport $zip,
        Table4Report $table4,
    ): CrossTableReconciliationResult {
        $insurance = $table4->insurance;

        return new CrossTableReconciliationResult(
            table3aTotalPatients: $table3a->total(),
            table3bTotalPatients: $table3b->totalPatients(),
            zipTotalPatients: $zip->total(),
            table4IncomeTotal: $table4->income->total(),
            table4InsuranceTotal: $insurance->total(),
            zipUninsured: $zip->columnTotal(UdsZipInsuranceColumn::Uninsured),
            table4None: $insurance->categoryTotal(UdsPayerCategory::None),
            zipMedicaidChipOtherPublic: $zip->columnTotal(UdsZipInsuranceColumn::MedicaidChipOtherPublic),
            table4MedicaidPlusOtherPublic: $insurance->categoryTotal(UdsPayerCategory::Medicaid)
                + $insurance->categoryTotal(UdsPayerCategory::OtherPublic),
            zipMedicare: $zip->columnTotal(UdsZipInsuranceColumn::Medicare),
            table4Medicare: $insurance->categoryTotal(UdsPayerCategory::Medicare),
            zipPrivate: $zip->columnTotal(UdsZipInsuranceColumn::Private),
            table4Private: $insurance->categoryTotal(UdsPayerCategory::Private),
        );
    }
}
