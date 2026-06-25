<?php

/**
 * Computes a patient's income as a percentage of the Federal Poverty Level and
 * the corresponding UDS band.
 *
 * Pure and deterministic: given an income determination and a guideline it
 * returns a result, with no database, clock, or global state. Rules encoded
 * here (per the UDS Manual): missing/declined income yields Unknown (never a
 * guessed band), and the band is decided from the exact income ratio while the
 * displayed percentage is rounded.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

final class FplCalculator
{
    public function calculate(IncomeDetermination $income, FederalPovertyGuideline $guideline): FplResult
    {
        if (!$income->isDeterminable() || $income->householdSize === null || $income->annualIncome === null) {
            return new FplResult(null, FplBand::Unknown);
        }

        $threshold = $guideline->annualThresholdFor($income->householdSize);
        if ($threshold <= 0.0) {
            return new FplResult(null, FplBand::Unknown);
        }

        $ratioPercent = ($income->annualIncome / $threshold) * 100.0;

        return new FplResult(
            (int) round($ratioPercent),
            $this->bandFor($ratioPercent),
        );
    }

    private function bandFor(float $ratioPercent): FplBand
    {
        return match (true) {
            $ratioPercent <= 100.0 => FplBand::AtOrBelow100,
            $ratioPercent <= 150.0 => FplBand::From101To150,
            $ratioPercent <= 200.0 => FplBand::From151To200,
            default => FplBand::Above200,
        };
    }
}
