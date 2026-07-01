<?php

/**
 * Supplies the countable visits for a UDS Table 5 reporting year.
 *
 * Abstracting the visit source behind this interface keeps the report generator
 * pure and unit-testable: production uses the database-backed
 * Table5VisitRepository, while tests drive the aggregation with an in-memory
 * implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

interface Table5VisitSource
{
    /**
     * Every countable visit with a date of service in the reporting calendar
     * year, already classified to a UDS service category.
     *
     * @return list<Table5VisitRecord>
     */
    public function visitsForYear(int $year): array;
}
