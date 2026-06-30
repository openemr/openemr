<?php

/**
 * The four insurance columns of the UDS Patients by ZIP Code Table.
 *
 * The ZIP table groups primary insurance more coarsely than Table 4: it folds
 * Medicaid, CHIP, and Other Public into a single column (manual lines
 * 1339–1348). Mapping the five Table 4 payer categories onto these four columns
 * is what lets the cross-table reconciliation assert ZIP Column C = Table 4
 * Medicaid + Other Public, etc.
 *
 * Backed because it is reported. Matched exhaustively.
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

enum UdsZipInsuranceColumn: string
{
    case Uninsured = 'uninsured';
    case MedicaidChipOtherPublic = 'medicaid_chip_other_public';
    case Medicare = 'medicare';
    case Private = 'private';

    public static function fromPayerCategory(UdsPayerCategory $category): self
    {
        return match ($category) {
            UdsPayerCategory::None => self::Uninsured,
            UdsPayerCategory::Medicaid, UdsPayerCategory::OtherPublic => self::MedicaidChipOtherPublic,
            UdsPayerCategory::Medicare => self::Medicare,
            UdsPayerCategory::Private => self::Private,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Uninsured => 'Uninsured',
            self::MedicaidChipOtherPublic => 'Medicaid / CHIP / Other Public',
            self::Medicare => 'Medicare',
            self::Private => 'Private',
        };
    }
}
