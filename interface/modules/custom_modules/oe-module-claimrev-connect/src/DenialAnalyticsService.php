<?php

/**
 * Service for Denial Analytics.
 *
 * Analyzes denial patterns from ar_activity adjustment records and
 * ClaimRev tracking data. Groups by payer, reason code, and time period.
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
 * @phpstan-type DenialReasonRow array{reason: string, carcCode: string, carcDescription: string, count: int, totalAmount: float}
 * @phpstan-type DenialPayerRow array{payerName: string, count: int, totalAmount: float, encounterCount: int}
 * @phpstan-type DenialMonthRow array{month: string, count: int, totalAmount: float}
 * @phpstan-type DenialSummary array{totalAdjustments: int, totalAmount: float, affectedEncounters: int, payerCount: int}
 */
class DenialAnalyticsService
{
    /**
     * CARC (Claim Adjustment Reason Code) descriptions for common denial codes.
     */
    private const CARC_DESCRIPTIONS = [
        '1' => 'Deductible',
        '2' => 'Coinsurance',
        '3' => 'Copay',
        '4' => 'Contractual obligation',
        '5' => 'Tax amount',
        '16' => 'Claim/service lacks info needed for adjudication',
        '18' => 'Duplicate claim/service',
        '22' => 'Care may be covered by another payer',
        '23' => 'Charges covered under capitation',
        '24' => 'Charges covered by benefits under another plan',
        '26' => 'Expenses incurred prior to coverage',
        '27' => 'Expenses incurred after coverage',
        '29' => 'Time limit for filing has expired',
        '31' => 'Not our responsibility',
        '32' => 'Our contract/plan does not cover this',
        '33' => 'Claim lacked required pre-authorization',
        '35' => 'Lifetime benefit maximum reached',
        '39' => 'Services denied at the time of authorization',
        '45' => 'Charge exceeds fee schedule/max allowable',
        '49' => 'Non-covered service because routine/preventive',
        '50' => 'Non-covered service (not deemed medically necessary)',
        '55' => 'Procedure/treatment not included in benefits',
        '96' => 'Non-covered charge(s)',
        '97' => 'Payment adjusted (already adjudicated)',
        '109' => 'Claim not covered by this payer/contractor',
        '119' => 'Benefit maximum for this time period has been reached',
        '167' => 'Diagnosis is not covered',
        '170' => 'Payment denied: no prior claim/encounter data',
        '197' => 'Precertification/authorization/notification absent',
        '204' => 'Service not authorized on this date of service',
        '223' => 'Adjustment based on payer-determined fee schedule',
        '226' => 'Info requested was not provided',
        '227' => 'Info requested was not provided timely',
        '242' => 'Service not payable per managed care contract',
        '253' => 'Sequestration (federal mandate)',
    ];

    /**
     * Get denial analytics data.
     *
     * @param array{dateStart?: string, dateEnd?: string, payerName?: string} $filters
     * @return array{byReason: list<DenialReasonRow>, byPayer: list<DenialPayerRow>, byMonth: list<DenialMonthRow>, summary: DenialSummary}
     */
    public static function getAnalytics(array $filters = []): array
    {
        $where = [];
        $params = [];

        $dateStart = ($filters['dateStart'] ?? '') !== '' ? $filters['dateStart'] : date('Y-m-d', strtotime('-12 months'));
        $dateEnd = ($filters['dateEnd'] ?? '') !== '' ? $filters['dateEnd'] : date('Y-m-d');

        $where[] = "a.post_time >= ?";
        $params[] = $dateStart . ' 00:00:00';
        $where[] = "a.post_time <= ?";
        $params[] = $dateEnd . ' 23:59:59';

        $payerNameFilter = $filters['payerName'] ?? '';
        if ($payerNameFilter !== '') {
            $where[] = "ic.name LIKE ?";
            $params[] = '%' . $payerNameFilter . '%';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Base FROM clause for all queries — joins ar_activity to payer info
        $baseFrom = "FROM ar_activity a " .
            "JOIN ar_session s ON s.session_id = a.session_id " .
            "LEFT JOIN insurance_companies ic ON ic.id = s.payer_id " .
            $whereClause . " AND a.deleted IS NULL AND a.adj_amount != 0 AND a.memo != '' ";

        // By reason code (top 20)
        $byReason = QueryUtils::fetchRecords(
            "SELECT a.memo AS reason, COUNT(*) AS denial_count, SUM(ABS(a.adj_amount)) AS total_amount " .
            $baseFrom .
            "GROUP BY a.memo ORDER BY denial_count DESC LIMIT 20",
            $params
        );

        // Enrich with CARC descriptions
        $byReason = array_map(function (array $r): array {
            $reason = self::asString($r['reason'] ?? '');
            $carcCode = '';
            if (preg_match('/Adjust code (\d+)/', $reason, $m)) {
                $carcCode = $m[1];
            }
            return [
                'reason' => $reason,
                'carcCode' => $carcCode,
                'carcDescription' => self::CARC_DESCRIPTIONS[$carcCode] ?? '',
                'count' => self::asInt($r['denial_count'] ?? 0),
                'totalAmount' => round(self::asFloat($r['total_amount'] ?? 0), 2),
            ];
        }, $byReason);

        // By payer (top 20)
        $byPayerRows = QueryUtils::fetchRecords(
            "SELECT COALESCE(ic.name, 'Unknown') AS payer_name, " .
            "COUNT(*) AS denial_count, " .
            "SUM(ABS(a.adj_amount)) AS total_amount, " .
            "COUNT(DISTINCT CONCAT(a.pid, '-', a.encounter)) AS encounter_count " .
            $baseFrom .
            "GROUP BY payer_name ORDER BY denial_count DESC LIMIT 20",
            $params
        );
        $byPayer = array_map(fn(array $r): array => [
            'payerName' => self::asString($r['payer_name'] ?? ''),
            'count' => self::asInt($r['denial_count'] ?? 0),
            'totalAmount' => round(self::asFloat($r['total_amount'] ?? 0), 2),
            'encounterCount' => self::asInt($r['encounter_count'] ?? 0),
        ], $byPayerRows);

        // By month (trend)
        $byMonthRows = QueryUtils::fetchRecords(
            "SELECT DATE_FORMAT(a.post_time, '%Y-%m') AS month, " .
            "COUNT(*) AS denial_count, " .
            "SUM(ABS(a.adj_amount)) AS total_amount " .
            $baseFrom .
            "GROUP BY month ORDER BY month",
            $params
        );
        $byMonth = array_map(fn(array $r): array => [
            'month' => self::asString($r['month'] ?? ''),
            'count' => self::asInt($r['denial_count'] ?? 0),
            'totalAmount' => round(self::asFloat($r['total_amount'] ?? 0), 2),
        ], $byMonthRows);

        // Summary
        $summaryRow = QueryUtils::fetchRecords(
            "SELECT COUNT(*) AS total_adjustments, " .
            "SUM(ABS(a.adj_amount)) AS total_amount, " .
            "COUNT(DISTINCT CONCAT(a.pid, '-', a.encounter)) AS affected_encounters, " .
            "COUNT(DISTINCT s.payer_id) AS payer_count " .
            $baseFrom,
            $params
        );
        $s = $summaryRow[0] ?? [];

        return [
            'byReason' => $byReason,
            'byPayer' => $byPayer,
            'byMonth' => $byMonth,
            'summary' => [
                'totalAdjustments' => self::asInt($s['total_adjustments'] ?? 0),
                'totalAmount' => round(self::asFloat($s['total_amount'] ?? 0), 2),
                'affectedEncounters' => self::asInt($s['affected_encounters'] ?? 0),
                'payerCount' => self::asInt($s['payer_count'] ?? 0),
            ],
        ];
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
     * Export denial data as CSV.
     *
     * @param list<DenialReasonRow> $byReason
     */
    public static function toCsv(array $byReason): string
    {
        $output = "Reason,CARC Code,Description,Count,Total Amount\n";
        foreach ($byReason as $r) {
            $output .= '"' . str_replace('"', '""', $r['reason']) . '",';
            $output .= $r['carcCode'] . ',';
            $output .= '"' . str_replace('"', '""', $r['carcDescription']) . '",';
            $output .= $r['count'] . ',';
            $output .= $r['totalAmount'] . "\n";
        }
        return $output;
    }
}
