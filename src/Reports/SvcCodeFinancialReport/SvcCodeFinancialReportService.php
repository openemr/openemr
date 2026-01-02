<?php

/**
 * Service Code Financial Report Service
 *
 * @package   H.E.Project_v3
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 */

namespace OpenEMR\Reports\SvcCodeFinancialReport;

use OpenEMR\Common\Database\QueryUtils;

class SvcCodeFinancialReportService
{
    /**
     * Retrieve procedure code financial data
     *
     * @param string $fromDate Start date in YYYY-MM-DD format
     * @param string $toDate End date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility ID filter
     * @param int|null $providerId Optional provider ID filter
     * @param bool $importantCodesOnly Whether to show only important codes
     * @return array Array of procedure codes with financial metrics
     */
    public function getProcedureCodeFinancials(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null,
        bool $importantCodesOnly = false
    ): array {
        $sqlBindArray = [];

        $query = "SELECT b.code,
                    SUM(b.units) as units,
                    SUM(b.fee) as billed,
                    SUM(COALESCE(ar_act.paid, 0)) as paid_amount,
                    SUM(COALESCE(ar_act.adjust, 0)) as adjust_amount,
                    (SUM(b.fee) - (SUM(COALESCE(ar_act.paid, 0)) + SUM(COALESCE(ar_act.adjust, 0)))) as balance,
                    c.financial_reporting,
                    COUNT(DISTINCT CONCAT(b.pid, '_', b.encounter)) as encounter_count
                FROM form_encounter as fe
                JOIN billing as b ON b.pid = fe.pid AND b.encounter = fe.encounter
                LEFT JOIN (
                    SELECT pid, encounter, code,
                           SUM(pay_amount) as paid,
                           SUM(adj_amount) as adjust
                    FROM ar_activity
                    WHERE deleted IS NULL
                    GROUP BY pid, encounter, code
                ) as ar_act ON ar_act.pid = b.pid AND ar_act.encounter = b.encounter AND ar_act.code = b.code
                LEFT OUTER JOIN codes AS c ON c.code = b.code
                INNER JOIN code_types AS ct ON ct.ct_key = b.code_type AND ct.ct_fee = '1'
                WHERE b.code_type != 'COPAY'
                  AND b.activity = 1
                  AND fe.date >= ?
                  AND fe.date <= ?";

        array_push($sqlBindArray, "{$fromDate} 00:00:00", "{$toDate} 23:59:59");

        if ($facilityId) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId) {
            $query .= " AND b.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        if ($importantCodesOnly) {
            $query .= " AND c.financial_reporting = '1'";
        }

        $query .= " GROUP BY b.code ORDER BY SUM(b.units) DESC";

        $results = QueryUtils::fetchRecords($query, $sqlBindArray);

        return $results ?? [];
    }

    /**
     * Calculate summary metrics from procedure codes
     *
     * @param array $procedureCodes Array of procedure code records
     * @return array Summary metrics (totals and calculations)
     */
    public function calculateSummaryMetrics(array $procedureCodes): array
    {
        $totals = [
            'total_units' => 0,
            'total_billed' => 0,
            'total_paid' => 0,
            'total_adjustment' => 0,
            'total_balance' => 0,
            'total_encounters' => 0,
        ];

        foreach ($procedureCodes as $code) {
            $totals['total_units'] += $code['units'] ?? 0;
            $totals['total_billed'] += $code['billed'] ?? 0;
            $totals['total_paid'] += $code['paid_amount'] ?? 0;
            $totals['total_adjustment'] += $code['adjust_amount'] ?? 0;
            $totals['total_balance'] += $code['balance'] ?? 0;
            $totals['total_encounters'] += $code['encounter_count'] ?? 0;
        }

        // Calculate metrics
        $totals['collection_rate'] = $totals['total_billed'] > 0
            ? ($totals['total_paid'] / $totals['total_billed']) * 100
            : 0;

        $totals['average_per_unit'] = $totals['total_units'] > 0
            ? $totals['total_billed'] / $totals['total_units']
            : 0;

        return $totals;
    }

    /**
     * Prepare data for chart visualization
     *
     * @param array $procedureCodes Array of procedure code records
     * @param int $maxItems Maximum number of items to show in charts
     * @return array Data structured for Chart.js
     */
    public function prepareChartData(array $procedureCodes, int $maxItems = 15): array
    {
        // Top procedures by volume
        $volumeData = array_slice($procedureCodes, 0, $maxItems);

        return [
            'volume_by_code' => [
                'labels' => array_map(fn($item) => $item['code'], $volumeData),
                'datasets' => [
                    [
                        'label' => 'Units',
                        'data' => array_map(fn($item) => $item['units'], $volumeData),
                        'backgroundColor' => $this->generateColors(count($volumeData)),
                        'borderColor' => '#ddd',
                        'borderWidth' => 1,
                    ]
                ]
            ],
            'revenue_distribution' => [
                'labels' => array_map(fn($item) => $item['code'], array_slice($procedureCodes, 0, 10)),
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => array_map(fn($item) => $item['billed'], array_slice($procedureCodes, 0, 10)),
                        'backgroundColor' => [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ]
                    ]
                ]
            ],
            'collection_by_code' => [
                'labels' => array_map(fn($item) => $item['code'], array_slice($procedureCodes, 0, 15)),
                'datasets' => [
                    [
                        'label' => 'Billed',
                        'data' => array_map(fn($item) => $item['billed'], array_slice($procedureCodes, 0, 15)),
                        'backgroundColor' => '#FF9999'
                    ],
                    [
                        'label' => 'Paid',
                        'data' => array_map(fn($item) => $item['paid_amount'], array_slice($procedureCodes, 0, 15)),
                        'backgroundColor' => '#99FF99'
                    ],
                    [
                        'label' => 'Balance',
                        'data' => array_map(fn($item) => $item['balance'], array_slice($procedureCodes, 0, 15)),
                        'backgroundColor' => '#FFFF99'
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate random colors for chart visualization
     *
     * @param int $count Number of colors to generate
     * @return array Array of hex color codes
     */
    private function generateColors(int $count): array
    {
        $colors = [];
        $baseColors = [
            '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
            '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140',
            '#30b0fe', '#4099ff', '#73b8ff', '#a8d8ff', '#c2e0ff'
        ];

        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }

    /**
     * Format procedure code data for display
     *
     * @param array $procedureCodes Array of procedure code records
     * @return array Formatted data with HTML-safe strings
     */
    public function formatForDisplay(array $procedureCodes): array
    {
        return array_map(fn($item) => [
            'code' => $item['code'] ?? '',
            'units' => (int)($item['units'] ?? 0),
            'billed' => (float)($item['billed'] ?? 0),
            'paid_amount' => (float)($item['paid_amount'] ?? 0),
            'adjust_amount' => (float)($item['adjust_amount'] ?? 0),
            'balance' => (float)($item['balance'] ?? 0),
            'encounter_count' => (int)($item['encounter_count'] ?? 0),
            'financial_reporting' => $item['financial_reporting'] ?? 0,
            'collection_rate' => ($item['billed'] ?? 0) > 0
                ? (($item['paid_amount'] ?? 0) / ($item['billed'] ?? 0)) * 100
                : 0,
            'revenue_per_unit' => ($item['units'] ?? 0) > 0
                ? ($item['billed'] ?? 0) / ($item['units'] ?? 0)
                : 0
        ], $procedureCodes);
    }
}
