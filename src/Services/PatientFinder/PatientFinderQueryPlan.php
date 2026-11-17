<?php

/**
 * PatientFinderQueryPlan
 *
 * The SQL produced by {@see PatientFinderQueryBuilder}: the paged data query and
 * the two DataTables count queries (total and post-filter), each with its bound
 * values in parameter order. It is a plain carrier — building it touches no
 * database; executing it is the caller's job.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final readonly class PatientFinderQueryPlan
{
    /**
     * @param string                  $dataSql           SELECT for the current page of rows.
     * @param array<int, string|int>  $dataBinds         Bind values for $dataSql, in order.
     * @param string                  $totalCountSql     COUNT for all rows (filter clause only).
     * @param array<int, string|int>  $totalCountBinds   Bind values for $totalCountSql, in order.
     * @param string                  $filteredCountSql  COUNT for rows after search + filter.
     * @param array<int, string|int>  $filteredCountBinds Bind values for $filteredCountSql, in order.
     */
    public function __construct(
        public string $dataSql,
        public array $dataBinds,
        public string $totalCountSql,
        public array $totalCountBinds,
        public string $filteredCountSql,
        public array $filteredCountBinds
    ) {
    }
}
