<?php

/**
 * A Sliding Fee Discount Program (SFDP) tier.
 *
 * Derived from the FPL band. The actual breakpoints and nominal fees are
 * configurable per health center (within HRSA rules); these cases name the
 * default schedule's tiers. Unit enum — exhaustively matched.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

enum SlidingFeeTier
{
    case NominalFee;     // ≤100% FPL — nominal/flat fee
    case Discount;       // 101–150% FPL
    case PartialDiscount; // 151–200% FPL
    case FullCharge;     // >200% FPL — no discount
    case Undetermined;   // income unknown

    public function label(): string
    {
        return match ($this) {
            self::NominalFee => 'Nominal fee',
            self::Discount => 'Sliding fee — discount',
            self::PartialDiscount => 'Sliding fee — partial discount',
            self::FullCharge => 'Full charge',
            self::Undetermined => 'Undetermined',
        };
    }
}
