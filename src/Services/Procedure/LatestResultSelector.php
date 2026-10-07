<?php

/**
 * Chooses which report's results to show for a result code when an order's
 * results are consolidated to the latest ones ("finals only").
 *
 * Reports are read in report-date order, so a later report normally replaces
 * an earlier one. When two reports share a report date, the result date
 * decides: a report whose results are older than the ones already kept does
 * not replace them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Procedure;

final class LatestResultSelector
{
    /**
     * Whether the results already kept should stay, instead of being replaced
     * by the results of the report being read now.
     *
     * @param array<array-key, mixed> $keptReport  procedure_report row of the kept results
     * @param list<mixed>             $keptResults procedure_result rows kept for this result code
     * @param array<array-key, mixed> $report      procedure_report row being read
     * @param list<mixed>             $results     its procedure_result rows for this result code
     */
    public static function keepsEarlierReport(array $keptReport, array $keptResults, array $report, array $results): bool
    {
        $reportDate = $report['date_report'] ?? null;
        if (!is_string($reportDate) || $reportDate !== ($keptReport['date_report'] ?? null)) {
            return false;
        }
        $resultRow = $results[0] ?? null;
        $keptRow = $keptResults[0] ?? null;
        if (!is_array($resultRow) || !is_array($keptRow)) {
            return false;
        }
        $resultDate = $resultRow['date'] ?? null;
        $keptResultDate = $keptRow['date'] ?? null;
        if (!is_string($resultDate) || $resultDate === '' || !is_string($keptResultDate) || $keptResultDate === '') {
            return false;
        }
        return $resultDate < $keptResultDate;
    }
}
