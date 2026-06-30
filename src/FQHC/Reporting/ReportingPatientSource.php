<?php

/**
 * Supplies the patient cohort and per-patient reporting inputs for a UDS year.
 *
 * Abstracting the data source behind this interface keeps the report generator
 * pure and unit-testable: production uses the database-backed
 * ReportingPatientRepository, while tests drive the whole pipeline with an
 * in-memory implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

interface ReportingPatientSource
{
    /**
     * Patient ids with at least one countable visit in the reporting calendar
     * year (the UDS unduplicated patient cohort).
     *
     * @return list<int>
     */
    public function cohortForYear(int $year): array;

    /**
     * Resolve one patient's reporting inputs for the given reporting year.
     */
    public function load(int $pid, int $year): ReportingPatient;
}
