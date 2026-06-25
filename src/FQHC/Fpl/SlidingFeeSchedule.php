<?php

/**
 * Maps an FPL band to a Sliding Fee Discount Program tier.
 *
 * This is the HRSA-style default schedule (nominal fee at/below 100% FPL,
 * graduated discounts to 200%, full charge above). Health centers may configure
 * their own breakpoints within HRSA rules; that configurable schedule replaces
 * this default in a later step. Exhaustive `match` on the band so a new band
 * forces a decision here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

final class SlidingFeeSchedule
{
    public function tierFor(FplBand $band): SlidingFeeTier
    {
        return match ($band) {
            FplBand::AtOrBelow100 => SlidingFeeTier::NominalFee,
            FplBand::From101To150 => SlidingFeeTier::Discount,
            FplBand::From151To200 => SlidingFeeTier::PartialDiscount,
            FplBand::Above200 => SlidingFeeTier::FullCharge,
            FplBand::Unknown => SlidingFeeTier::Undetermined,
        };
    }
}
