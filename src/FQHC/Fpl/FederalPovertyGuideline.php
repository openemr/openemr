<?php

/**
 * A Federal Poverty Level guideline for a given year and region.
 *
 * The guideline is a base annual amount for a one-person household plus a fixed
 * increment per additional person. These figures change every year and differ
 * by region, so they are stored as versioned data (seeded into
 * `fqhc_fpl_guideline`) and loaded into this value object — never hard-coded
 * into logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

use DomainException;

final readonly class FederalPovertyGuideline
{
    public function __construct(
        public int $year,
        public FplRegion $region,
        public float $baseAnnual,
        public float $perPersonAnnual,
    ) {
        if ($baseAnnual <= 0.0) {
            throw new DomainException('FPL base annual amount must be positive');
        }
        if ($perPersonAnnual < 0.0) {
            throw new DomainException('FPL per-person amount cannot be negative');
        }
    }

    /**
     * The 100%-FPL annual income threshold for a household of the given size.
     */
    public function annualThresholdFor(int $householdSize): float
    {
        $size = max(1, $householdSize);

        return $this->baseAnnual + ($size - 1) * $this->perPersonAnnual;
    }
}
