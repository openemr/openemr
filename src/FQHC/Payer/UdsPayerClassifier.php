<?php

/**
 * Classifies an OpenEMR insurance type code into a UDS payer category.
 *
 * The code is `insurance_companies.ins_type_code` — a 1-based id into OpenEMR's
 * `insurance_type_codes` list. This is the out-of-the-box default mapping;
 * per-company overrides are a later enhancement. Returns null for an unknown or
 * missing code so the UI can flag it as unclassified rather than guessing a
 * bucket (UDS numbers must not be fabricated).
 *
 * Reporting-layer note (epic #4): Table 4 has no "Unknown"/"Unclassified"
 * insurance column — every counted patient lands in one of the five buckets.
 * The report generator must therefore coerce a null here to None/Uninsured
 * (Line 7) at counting time, and apply the principal-insurance tie-breaks the
 * single-code mapping cannot (dually eligible → Medicare; Medicaid/CHIP managed
 * care administered by a private plan → Medicaid/Other Public, not Private),
 * plus the 8a/8b, 9a, 10a/10b sub-line granularity. Those rules belong with the
 * report, not this Snapshot-facing classifier — see UDS-DATA-MODEL-VALIDATION.md §4.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

final class UdsPayerClassifier
{
    public function classifyByInsuranceTypeCode(?int $code): ?UdsPayerCategory
    {
        return match ($code) {
            3 => UdsPayerCategory::Medicaid,           // Medicaid
            2, 15 => UdsPayerCategory::Medicare,       // Medicare Part B; HMO Medicare Risk
            8 => UdsPayerCategory::None,               // Self Pay
            4, 5, 7, 10, 22, 23, 24, 25 => UdsPayerCategory::OtherPublic,
            // CHAMPVA, ChampUS/TRICARE, FECA, Other Non-Federal Programs,
            // Other Federal Program, Title V, VA Plan, Workers Compensation
            1, 6, 9, 11, 12, 13, 14, 16, 17, 18, 19, 20, 21, 26 => UdsPayerCategory::Private,
            default => null,
        };
    }
}
