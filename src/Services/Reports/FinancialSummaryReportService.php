<?php

/**
 * Query logic for the Financial Summary by Service Code report.
 *
 * Fixes a bug where the original LEFT JOIN on the codes table matched only
 * on `code`, producing a Cartesian product when multiple rows shared the
 * same code value (different modifiers or code types). The fix uses a
 * subquery grouped by (code, code_type) and joins on both columns.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Reports;

use OpenEMR\Common\Database\QueryUtils;

class FinancialSummaryReportService
{
    /**
     * Get financial summary data grouped by service code.
     *
     * @param ?int $facilityId Filter by facility (null = all)
     * @param ?int $providerId Filter by provider (null = all)
     * @param bool $financialReportingOnly Limit to codes flagged for financial reporting
     * @return ServiceCodeSummary[]
     */
    public function getServiceCodeSummary(
        \DateTimeInterface $fromDate,
        \DateTimeInterface $toDate,
        ?int $facilityId = null,
        ?int $providerId = null,
        bool $financialReportingOnly = false
    ): array {
        $query = <<<'SQL'
            SELECT
                b.code,
                SUM(b.units) AS units,
                SUM(b.fee) AS billed,
                SUM(ar_act.paid) AS paid,
                SUM(ar_act.adjusted) AS adjusted,
                MAX(c.financial_reporting) AS financial_reporting
            FROM form_encounter AS fe
            JOIN billing AS b
                ON b.pid = fe.pid AND b.encounter = fe.encounter
            JOIN (
                SELECT pid, encounter, code,
                    SUM(pay_amount) AS paid, SUM(adj_amount) AS adjusted
                FROM ar_activity
                WHERE deleted IS NULL
                GROUP BY pid, encounter, code
            ) AS ar_act
                ON ar_act.pid = b.pid
                AND ar_act.encounter = b.encounter
                AND ar_act.code = b.code
            INNER JOIN code_types AS ct
                ON ct.ct_key = b.code_type AND ct.ct_fee = '1'
            LEFT OUTER JOIN (
                SELECT code, code_type,
                    MAX(financial_reporting) AS financial_reporting
                FROM codes
                GROUP BY code, code_type
            ) AS c
                ON c.code = b.code AND c.code_type = ct.ct_id
            SQL;

        $conditions = [
            "b.code_type != 'COPAY'",
            'b.activity = 1',
            'fe.date BETWEEN ? AND ?',
        ];
        $binds = [
            $fromDate->format('Y-m-d 00:00:00'),
            $toDate->format('Y-m-d 23:59:59'),
        ];

        if ($facilityId !== null) {
            $conditions[] = 'fe.facility_id = ?';
            $binds[] = $facilityId;
        }

        if ($providerId !== null) {
            $conditions[] = 'b.provider_id = ?';
            $binds[] = $providerId;
        }

        $query .= ' WHERE ' . implode(' AND ', $conditions);
        $query .= ' GROUP BY b.code';

        if ($financialReportingOnly) {
            $query .= " HAVING MAX(c.financial_reporting) = '1'";
        }

        $query .= ' ORDER BY b.code';

        /** @var array<int, array{code: string, units: string, billed: string, paid: string, adjusted: string, financial_reporting: string|null}> $records */
        $records = QueryUtils::fetchRecords($query, $binds);

        return array_map(ServiceCodeSummary::fromArray(...), $records);
    }
}
