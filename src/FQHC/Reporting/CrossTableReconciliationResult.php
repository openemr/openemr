<?php

/**
 * The UDS patient totals (and ZIP insurance-column totals) the manual requires
 * to agree across the patient tables, and whether they do.
 *
 * Per the manual's cross-table considerations (and UDS-DATA-MODEL-VALIDATION.md
 * §6): Table 3A Line 39 = Table 3B Line 8 = the ZIP Code Table total = Table 4
 * Line 6 (income) = Table 4 Line 12 (insurance); and on the ZIP table, Column B
 * (Uninsured) = Table 4 None, Column C (Medicaid/CHIP/Other Public) = Table 4
 * Medicaid + Other Public, Column D (Medicare) = Table 4 Medicare, Column E
 * (Private) = Table 4 Private (manual lines 1339–1348). Capturing both sides of
 * each equality makes a mismatch point straight at the disagreeing table.
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
        public int $table3bTotalPatients,
        public int $zipTotalPatients,
        public int $table4IncomeTotal,
        public int $table4InsuranceTotal,
        public int $zipUninsured,
        public int $table4None,
        public int $zipMedicaidChipOtherPublic,
        public int $table4MedicaidPlusOtherPublic,
        public int $zipMedicare,
        public int $table4Medicare,
        public int $zipPrivate,
        public int $table4Private,
    ) {
    }

    /**
     * The unduplicated patient total agrees across all four patient tables.
     */
    public function patientTotalsAgree(): bool
    {
        $totals = [
            $this->table3aTotalPatients,
            $this->table3bTotalPatients,
            $this->zipTotalPatients,
            $this->table4IncomeTotal,
            $this->table4InsuranceTotal,
        ];

        return count(array_unique($totals)) === 1;
    }

    /**
     * Each ZIP insurance column equals the corresponding Table 4 line(s).
     */
    public function zipColumnsAgreeWithTable4(): bool
    {
        return $this->zipUninsured === $this->table4None
            && $this->zipMedicaidChipOtherPublic === $this->table4MedicaidPlusOtherPublic
            && $this->zipMedicare === $this->table4Medicare
            && $this->zipPrivate === $this->table4Private;
    }

    public function isConsistent(): bool
    {
        return $this->patientTotalsAgree() && $this->zipColumnsAgreeWithTable4();
    }
}
