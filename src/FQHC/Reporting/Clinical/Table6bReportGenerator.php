<?php

/**
 * Generates the UDS Table 6B clinical-quality report for a reporting year.
 *
 * Orchestration only: it pulls computed population counts from an injected
 * CqmMeasureResultSource and hands them to the pure builder. Because the
 * source is an interface, this pipeline is unit-testable with an in-memory
 * source; production injects an engine-backed implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

final class Table6bReportGenerator
{
    private Table6bReportBuilder $builder;

    public function __construct(
        private CqmMeasureResultSource $source,
        ?Table6bReportBuilder $builder = null,
    ) {
        $this->builder = $builder ?? new Table6bReportBuilder();
    }

    public function generateForYear(int $year): Table6bReport
    {
        return $this->builder->build($year, $this->source->resultsForYear($year));
    }
}
