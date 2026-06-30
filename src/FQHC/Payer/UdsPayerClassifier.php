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
