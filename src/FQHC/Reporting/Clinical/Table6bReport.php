<?php

/**
 * A computed UDS Table 6B ("Clinical Quality Measures") for a reporting year.
 *
 * Wraps the CQM/AMC engine's population counts for each measure in the UDS
 * clinical measure map. A measure absent from the underlying results has not
 * been computed yet (see PendingCqmMeasureResultSource) and reports null
 * rather than a misleading zero.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

final readonly class Table6bReport
{
    /**
     * @param array<string, UdsMeasurePopulationCounts> $resultsByCmsId keyed by UdsClinicalMeasure::cmsId()
     */
    public function __construct(
        public int $year,
        private array $resultsByCmsId,
    ) {
    }

    public function resultFor(UdsClinicalMeasure $measure): ?UdsMeasurePopulationCounts
    {
        return $this->resultsByCmsId[$measure->cmsId()] ?? null;
    }

    public function isComputed(UdsClinicalMeasure $measure): bool
    {
        return $this->resultFor($measure) !== null;
    }
}
