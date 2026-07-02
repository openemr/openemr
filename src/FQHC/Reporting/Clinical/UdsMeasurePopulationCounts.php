<?php

/**
 * The eCQM proportion-measure population counts for one UDS clinical measure
 * in a reporting year: initial population, denominator (with its exclusions
 * and exceptions), and numerator — the same population types the CQM/AMC
 * engine already computes for MIPS/AMC submission.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

final readonly class UdsMeasurePopulationCounts
{
    public function __construct(
        public int $initialPopulation,
        public int $denominator,
        public int $denominatorExclusions,
        public int $denominatorExceptions,
        public int $numerator,
    ) {
    }

    /**
     * The denominator after removing exclusions and exceptions — the base the
     * reported rate is computed against.
     */
    public function eligibleDenominator(): int
    {
        return max(0, $this->denominator - $this->denominatorExclusions - $this->denominatorExceptions);
    }

    /**
     * The measure's performance rate, or null when no patient is eligible
     * (an empty denominator is "not applicable", not a 0% rate).
     */
    public function rate(): ?float
    {
        $eligible = $this->eligibleDenominator();

        return $eligible === 0 ? null : $this->numerator / $eligible;
    }
}
