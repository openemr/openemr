<?php

/**
 * Builds a Table6bReport from raw CQM engine results.
 *
 * Pure and deterministic: it only keeps entries for measures in the UDS
 * clinical measure map, so an unrelated or misconfigured CMS eCQM id in the
 * source never leaks into the report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

final class Table6bReportBuilder
{
    /**
     * @param array<string, UdsMeasurePopulationCounts> $rawResults keyed by CMS eCQM id
     */
    public function build(int $year, array $rawResults): Table6bReport
    {
        $results = [];
        foreach (UdsClinicalMeasure::cases() as $measure) {
            $result = $rawResults[$measure->cmsId()] ?? null;
            if ($result !== null) {
                $results[$measure->cmsId()] = $result;
            }
        }

        return new Table6bReport($year, $results);
    }
}
