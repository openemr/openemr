<?php

/**
 * Generates the UDS data-quality worklist for a reporting year.
 *
 * Orchestration only: it pulls the reporting cohort from an injected
 * ReportingPatientSource — the same source the UDS report itself uses — and
 * hands the resolved patients to the pure gap detector. Because the source is
 * an interface, this pipeline is unit-testable with an in-memory source;
 * production injects the existing database-backed repository.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

use OpenEMR\FQHC\Reporting\ReportingPatientSource;

final class DataQualityWorklistGenerator
{
    private DataQualityWorklistBuilder $builder;

    public function __construct(
        private ReportingPatientSource $source,
        ?DataQualityWorklistBuilder $builder = null,
    ) {
        $this->builder = $builder ?? new DataQualityWorklistBuilder();
    }

    public function generateForYear(int $year): DataQualityWorklist
    {
        $patients = [];
        foreach ($this->source->cohortForYear($year) as $pid) {
            $patients[] = $this->source->load($pid, $year);
        }

        return new DataQualityWorklist($year, $this->builder->build($patients));
    }
}
