<?php

/**
 * The computed data-quality worklist for a reporting year: every patient with
 * at least one gap, and how many patients have each gap type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

final readonly class DataQualityWorklist
{
    /**
     * @param list<PatientDataQualityIssues> $patients
     */
    public function __construct(
        public int $year,
        public array $patients,
    ) {
    }

    public function countOf(UdsDataQualityGap $gap): int
    {
        $count = 0;
        foreach ($this->patients as $issues) {
            if (in_array($gap, $issues->gaps, true)) {
                $count++;
            }
        }

        return $count;
    }

    public function total(): int
    {
        return count($this->patients);
    }
}
