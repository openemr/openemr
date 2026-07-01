<?php

/**
 * Generates the UDS Table 5 utilization report for a reporting year.
 *
 * Orchestration only: it pulls the year's countable visits from an injected
 * Table5VisitSource and aggregates them. Because the source is an interface, the
 * pipeline is unit-testable with an in-memory source; production injects the
 * database-backed repository.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class Table5ReportGenerator
{
    private Table5ReportBuilder $builder;

    public function __construct(
        private Table5VisitSource $source,
        ?Table5ReportBuilder $builder = null,
    ) {
        $this->builder = $builder ?? new Table5ReportBuilder();
    }

    public function generateForYear(int $year): Table5Report
    {
        return $this->builder->build($this->source->visitsForYear($year));
    }
}
