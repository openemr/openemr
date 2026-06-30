<?php

/**
 * One patient's residence and insurance for the UDS Patients by ZIP Code Table.
 *
 * Parsed reporting input: the residence (a resolved ZIP or Unknown) is set at
 * the data boundary. The payer category is nullable because an unrecognised
 * insurance type cannot be classified; the builder coerces that null to
 * Uninsured, since the ZIP table — like Table 4 — has no "unknown insurance"
 * column (UDS-DATA-MODEL-VALIDATION.md §4).
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

final readonly class ZipCodeTablePatientRecord
{
    public function __construct(
        public ZipResidence $residence,
        public ?UdsPayerCategory $payerCategory,
    ) {
    }
}
