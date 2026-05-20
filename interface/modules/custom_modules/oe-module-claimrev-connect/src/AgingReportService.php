<?php

/**
 * Service for AR Aging Report.
 *
 * Calculates 30/60/90/120/120+ day aging buckets from OpenEMR billing
 * data, grouped by payer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;

/**
 * @phpstan-type AgingTotals array{current: float, days30: float, days60: float, days90: float, days120: float, days120plus: float, total: float}
 * @phpstan-type AgingPayerRow array{payerName: string, payerId: ?int, current: float, days30: float, days60: float, days90: float, days120: float, days120plus: float, total: float, encounterCount: int}
 * @phpstan-type AgingEncounter array{pid: int, encounter: int, encounterDate: string, patientName: string, payerName: string, ageDays: int, bucket: string, balance: float, lastLevelClosed: int, stmtCount: int}
 */
class AgingReportService
{
    /**
     * Get aging report grouped by payer.
     *
     * @param array{payerName?: string, patientName?: string, minAmount?: string} $filters
     * @return array{payers: list<AgingPayerRow>, totals: AgingTotals, encounters: list<AgingEncounter>}
     */
    public static function getAgingReport(array $filters = []): array
    {
        $where = ["fe.date >= DATE_SUB(NOW(), INTERVAL 730 DAY)"];
        $params = [];

        $payerNameFilter = $filters['payerName'] ?? '';
        if ($payerNameFilter !== '') {
            $where[] = "ic.name LIKE ?";
            $params[] = '%' . $payerNameFilter . '%';
        }
        $patientNameFilter = $filters['patientName'] ?? '';
        if ($patientNameFilter !== '') {
            $where[] = "(p.lname LIKE ? OR p.fname LIKE ?)";
            $params[] = '%' . $patientNameFilter . '%';
            $params[] = '%' . $patientNameFilter . '%';
        }
        $minAmountFilter = $filters['minAmount'] ?? '';
        $minAmount = $minAmountFilter !== '' ? (float) $minAmountFilter : 0.01;

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $balanceSql = "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            "+ COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            "- COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            "- COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0))";

        // Per-encounter detail with aging bucket
        $sql = "SELECT fe.pid, fe.encounter, fe.date AS encounter_date, " .
            "p.fname, p.lname, " .
            "COALESCE(ic.name, 'Self-Pay') AS payer_name, ic.id AS payer_id, " .
            "DATEDIFF(NOW(), fe.date) AS age_days, " .
            "$balanceSql AS balance, " .
            "fe.last_level_closed, fe.stmt_count " .
            "FROM form_encounter fe " .
            "JOIN patient_data p ON p.pid = fe.pid " .
            "LEFT JOIN insurance_data id ON id.pid = fe.pid AND id.type = 'primary' AND id.date <= fe.date " .
            "LEFT JOIN insurance_companies ic ON ic.id = id.provider " .
            $whereClause . " " .
            "GROUP BY fe.pid, fe.encounter " .
            "HAVING balance > ? " .
            "ORDER BY payer_name, age_days DESC";

        $rows = QueryUtils::fetchRecords($sql, array_merge($params, [$minAmount]));

        // Group by payer with aging buckets
        /** @var array<string, AgingPayerRow> $payerMap */
        $payerMap = [];
        $totals = [
            'current' => 0.0,
            'days30' => 0.0,
            'days60' => 0.0,
            'days90' => 0.0,
            'days120' => 0.0,
            'days120plus' => 0.0,
            'total' => 0.0,
        ];
        $encounters = [];

        foreach ($rows as $row) {
            $payerName = self::asString($row['payer_name'] ?? '') ?: 'Self-Pay';
            $balance = round(self::asFloat($row['balance'] ?? 0), 2);
            $ageDays = self::asInt($row['age_days'] ?? 0);
            $bucket = self::getBucket($ageDays);

            if (!isset($payerMap[$payerName])) {
                $payerId = $row['payer_id'] ?? null;
                $payerMap[$payerName] = [
                    'payerName' => $payerName,
                    'payerId' => is_numeric($payerId) ? (int) $payerId : null,
                    'current' => 0.0,
                    'days30' => 0.0,
                    'days60' => 0.0,
                    'days90' => 0.0,
                    'days120' => 0.0,
                    'days120plus' => 0.0,
                    'total' => 0.0,
                    'encounterCount' => 0,
                ];
            }

            $payerMap[$payerName][$bucket] += $balance;
            $payerMap[$payerName]['total'] += $balance;
            $payerMap[$payerName]['encounterCount']++;

            $totals[$bucket] += $balance;
            $totals['total'] += $balance;

            $lname = self::asString($row['lname'] ?? '');
            $fname = self::asString($row['fname'] ?? '');
            $encounters[] = [
                'pid' => self::asInt($row['pid'] ?? 0),
                'encounter' => self::asInt($row['encounter'] ?? 0),
                'encounterDate' => substr(self::asString($row['encounter_date'] ?? ''), 0, 10),
                'patientName' => $lname . ', ' . $fname,
                'payerName' => $payerName,
                'ageDays' => $ageDays,
                'bucket' => $bucket,
                'balance' => $balance,
                'lastLevelClosed' => self::asInt($row['last_level_closed'] ?? 0),
                'stmtCount' => self::asInt($row['stmt_count'] ?? 0),
            ];
        }

        // Sort payers by total descending
        $payers = array_values($payerMap);
        usort($payers, fn(array $a, array $b): int => $b['total'] <=> $a['total']);

        // Round totals
        foreach ($totals as $key => $v) {
            $totals[$key] = round($v, 2);
        }

        return ['payers' => $payers, 'totals' => $totals, 'encounters' => $encounters];
    }

    private static function asString(mixed $v): string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }
        return '';
    }

    private static function asInt(mixed $v): int
    {
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (int) $v;
        }
        if (is_float($v)) {
            return (int) $v;
        }
        return 0;
    }

    private static function asFloat(mixed $v): float
    {
        if (is_float($v)) {
            return $v;
        }
        if (is_int($v)) {
            return (float) $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (float) $v;
        }
        return 0.0;
    }

    /**
     * Get the aging bucket name for a given age in days.
     *
     * @return 'current'|'days30'|'days60'|'days90'|'days120'|'days120plus'
     */
    private static function getBucket(int $ageDays): string
    {
        if ($ageDays <= 30) {
            return 'current';
        }
        if ($ageDays <= 60) {
            return 'days30';
        }
        if ($ageDays <= 90) {
            return 'days60';
        }
        if ($ageDays <= 120) {
            return 'days90';
        }
        if ($ageDays <= 150) {
            return 'days120';
        }
        return 'days120plus';
    }

    /**
     * Export aging data as CSV string.
     *
     * @param list<AgingEncounter> $encounters
     */
    public static function toCsv(array $encounters): string
    {
        $output = "Patient,Encounter,Service Date,Payer,Age Days,Bucket,Balance,Ins Level,Stmts Sent\n";
        foreach ($encounters as $enc) {
            $output .= '"' . str_replace('"', '""', $enc['patientName']) . '",';
            $output .= $enc['encounter'] . ',';
            $output .= $enc['encounterDate'] . ',';
            $output .= '"' . str_replace('"', '""', $enc['payerName']) . '",';
            $output .= $enc['ageDays'] . ',';
            $output .= $enc['bucket'] . ',';
            $output .= $enc['balance'] . ',';
            $output .= $enc['lastLevelClosed'] . ',';
            $output .= $enc['stmtCount'] . "\n";
        }
        return $output;
    }
}
