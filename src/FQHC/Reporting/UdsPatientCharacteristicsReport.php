<?php

/**
 * The bundled UDS patient-characteristics output for one reporting year.
 *
 * Carries the four patient tables, the cross-table reconciliation result, and
 * the cohort size. Because a patient with no usable age or sex is dropped from
 * Tables 3A and 4, comparing `cohortSize` against the reconciled totals surfaces
 * exactly how many records need data-quality cleanup before submission.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class UdsPatientCharacteristicsReport
{
    public function __construct(
        public int $year,
        public int $cohortSize,
        public Table3aReport $table3a,
        public Table3bReport $table3b,
        public ZipCodeTableReport $zipCodeTable,
        public Table4Report $table4,
        public CrossTableReconciliationResult $reconciliation,
    ) {
    }
}
