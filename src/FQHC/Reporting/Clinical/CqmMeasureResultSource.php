<?php

/**
 * Supplies computed CQM/AMC engine population counts for the UDS clinical
 * measure map, for a reporting year.
 *
 * Abstracting the engine behind this interface keeps Table6bReportGenerator
 * pure and unit-testable, and keeps this package additive: nothing in
 * src/Cqm or src/Services/Qdm is modified to support UDS reporting. Production
 * wires a concrete engine-backed implementation once the per-measure
 * population-set selection (see PendingCqmMeasureResultSource) is specified;
 * tests drive the report with an in-memory implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

interface CqmMeasureResultSource
{
    /**
     * @return array<string, UdsMeasurePopulationCounts> population counts
     *     keyed by CMS eCQM id (UdsClinicalMeasure::cmsId()); a measure with
     *     no entry has not been computed for the year.
     */
    public function resultsForYear(int $year): array;
}
