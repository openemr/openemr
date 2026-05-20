<?php

/**
 * Service for the Recoupment Report.
 *
 * Identifies claims where payments were reversed (recouped) — typically
 * from Medicare reprocessing — and shows the original payment, recoupment,
 * any reprocessed payment, and the net impact.
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
 * @phpstan-type RecoupPayment array{amount: float, date: string, reference: string, memo: string}
 * @phpstan-type RecoupRow array{pid: int, encounter: int, patientName: string, patientDob: string, encounterDate: string, payerName: string, code: string, recoupAmount: float, recoupDate: string, recoupReference: string, recoupCheckDate: string, recoupMemo: string, originalTotal: float, reprocessedTotal: float, netImpact: float, currentBalance: float, hasReprocessed: bool, originalPayments: list<RecoupPayment>, reprocessedPayments: list<RecoupPayment>}
 * @phpstan-type RecoupSummary array{count: int, totalRecouped: float, totalOriginal: float, totalReprocessed: float, netImpact: float, pendingReprocess: int}
 */
class RecoupmentReportService
{
    /**
     * Get encounters that have had recoupments (negative payments).
     *
     * @param array{dateStart?: string, dateEnd?: string, payerName?: string, patientName?: string} $filters
     * @return array{recoupments: list<RecoupRow>, summary: RecoupSummary}
     */
    public static function getRecoupmentReport(array $filters = []): array
    {
        $where = ["a.pay_amount < 0", "a.deleted IS NULL"];
        $params = [];

        $dateStart = $filters['dateStart'] ?? '';
        if ($dateStart !== '') {
            $where[] = "a.post_time >= ?";
            $params[] = $dateStart . ' 00:00:00';
        }
        $dateEnd = $filters['dateEnd'] ?? '';
        if ($dateEnd !== '') {
            $where[] = "a.post_time <= ?";
            $params[] = $dateEnd . ' 23:59:59';
        }
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

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Find all encounters with negative payments (recoupments)
        $sql = "SELECT a.pid, a.encounter, a.code, a.modifier, " .
            "a.pay_amount AS recoup_amount, a.memo AS recoup_memo, " .
            "a.post_time AS recoup_date, a.session_id AS recoup_session_id, " .
            "s.reference AS recoup_reference, s.check_date AS recoup_check_date, " .
            "p.fname, p.lname, p.DOB, " .
            "fe.date AS encounter_date, " .
            "ic.name AS payer_name " .
            "FROM ar_activity a " .
            "JOIN ar_session s ON s.session_id = a.session_id " .
            "JOIN patient_data p ON p.pid = a.pid " .
            "JOIN form_encounter fe ON fe.pid = a.pid AND fe.encounter = a.encounter " .
            "LEFT JOIN insurance_companies ic ON ic.id = s.payer_id " .
            $whereClause . " " .
            "ORDER BY a.post_time DESC";

        $rows = QueryUtils::fetchRecords($sql, $params);

        // For each recoupment, find the original payment and any reprocessed payment
        $recoupments = [];
        $totalRecouped = 0.0;
        $totalOriginal = 0.0;
        $totalReprocessed = 0.0;

        foreach ($rows as $row) {
            $pid = TypeCoerce::asInt($row['pid'] ?? 0);
            $encounter = TypeCoerce::asInt($row['encounter'] ?? 0);
            $recoupAmount = round(TypeCoerce::asFloat($row['recoup_amount'] ?? 0), 2); // negative
            $recoupSessionId = TypeCoerce::asInt($row['recoup_session_id'] ?? 0);

            // Get all positive payments for this encounter to find original and reprocessed
            $payments = QueryUtils::fetchRecords(
                "SELECT a.pay_amount, a.post_time, a.memo, a.code, a.session_id, " .
                "s.reference, s.check_date " .
                "FROM ar_activity a " .
                "JOIN ar_session s ON s.session_id = a.session_id " .
                "WHERE a.pid = ? AND a.encounter = ? AND a.pay_amount > 0 AND a.deleted IS NULL " .
                "ORDER BY a.post_time",
                [$pid, $encounter]
            );

            // Classify payments as original (before recoup) or reprocessed (after recoup)
            $recoupDateRaw = TypeCoerce::asString($row['recoup_date'] ?? '');
            $originalPayments = [];
            $reprocessedPayments = [];
            $originalTotal = 0.0;
            $reprocessedTotal = 0.0;

            foreach ($payments as $pmt) {
                $pmtAmount = round(TypeCoerce::asFloat($pmt['pay_amount'] ?? 0), 2);
                $postTime = TypeCoerce::asString($pmt['post_time'] ?? '');
                $sessionId = TypeCoerce::asInt($pmt['session_id'] ?? 0);
                if ($postTime <= $recoupDateRaw && $sessionId !== $recoupSessionId) {
                    $originalPayments[] = $pmt;
                    $originalTotal += $pmtAmount;
                } elseif ($postTime > $recoupDateRaw) {
                    $reprocessedPayments[] = $pmt;
                    $reprocessedTotal += $pmtAmount;
                }
            }

            $netImpact = round($recoupAmount + $reprocessedTotal, 2);

            // Get current encounter balance
            $balance = self::getEncounterBalance($pid, $encounter);

            $recoupments[] = [
                'pid' => $pid,
                'encounter' => $encounter,
                'patientName' => TypeCoerce::asString($row['lname'] ?? '') . ', ' . TypeCoerce::asString($row['fname'] ?? ''),
                'patientDob' => substr(TypeCoerce::asString($row['DOB'] ?? ''), 0, 10),
                'encounterDate' => substr(TypeCoerce::asString($row['encounter_date'] ?? ''), 0, 10),
                'payerName' => TypeCoerce::asString($row['payer_name'] ?? ''),
                'code' => TypeCoerce::asString($row['code'] ?? ''),
                'recoupAmount' => $recoupAmount,
                'recoupDate' => substr($recoupDateRaw, 0, 10),
                'recoupReference' => TypeCoerce::asString($row['recoup_reference'] ?? ''),
                'recoupCheckDate' => TypeCoerce::asString($row['recoup_check_date'] ?? ''),
                'recoupMemo' => TypeCoerce::asString($row['recoup_memo'] ?? ''),
                'originalTotal' => round($originalTotal, 2),
                'reprocessedTotal' => round($reprocessedTotal, 2),
                'netImpact' => $netImpact,
                'currentBalance' => $balance,
                'hasReprocessed' => $reprocessedPayments !== [],
                'originalPayments' => array_map(fn(array $p): array => [
                    'amount' => round(TypeCoerce::asFloat($p['pay_amount'] ?? 0), 2),
                    'date' => substr(TypeCoerce::asString($p['post_time'] ?? ''), 0, 10),
                    'reference' => TypeCoerce::asString($p['reference'] ?? ''),
                    'memo' => TypeCoerce::asString($p['memo'] ?? ''),
                ], $originalPayments),
                'reprocessedPayments' => array_map(fn(array $p): array => [
                    'amount' => round(TypeCoerce::asFloat($p['pay_amount'] ?? 0), 2),
                    'date' => substr(TypeCoerce::asString($p['post_time'] ?? ''), 0, 10),
                    'reference' => TypeCoerce::asString($p['reference'] ?? ''),
                    'memo' => TypeCoerce::asString($p['memo'] ?? ''),
                ], $reprocessedPayments),
            ];

            $totalRecouped += $recoupAmount;
            $totalOriginal += $originalTotal;
            $totalReprocessed += $reprocessedTotal;
        }

        return [
            'recoupments' => $recoupments,
            'summary' => [
                'count' => count($recoupments),
                'totalRecouped' => round($totalRecouped, 2),
                'totalOriginal' => round($totalOriginal, 2),
                'totalReprocessed' => round($totalReprocessed, 2),
                'netImpact' => round($totalRecouped + $totalReprocessed, 2),
                'pendingReprocess' => count(array_filter($recoupments, fn(array $r): bool => !$r['hasReprocessed'])),
            ],
        ];
    }

    /**
     * Get current balance for an encounter.
     */
    private static function getEncounterBalance(int $pid, int $encounter): float
    {
        $row = QueryUtils::fetchRecords(
            "SELECT " .
            "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = ? AND b.encounter = ? AND b.activity = 1), 0) " .
            "+ COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = ? AND ds.encounter = ?), 0) " .
            "- COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = ? AND a.encounter = ? AND a.deleted IS NULL), 0) " .
            "- COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = ? AND a.encounter = ? AND a.deleted IS NULL), 0)" .
            ") AS balance",
            [$pid, $encounter, $pid, $encounter, $pid, $encounter, $pid, $encounter]
        );
        return round(TypeCoerce::asFloat($row[0]['balance'] ?? 0), 2);
    }

    /**
     * Export as CSV.
     *
     * @param list<RecoupRow> $recoupments
     */
    public static function toCsv(array $recoupments): string
    {
        $output = "Patient,Encounter,Service Date,Payer,Code,Original Paid,Recoup Amount,Recoup Date,Reference,Reprocessed,Net Impact,Current Balance,Status\n";
        foreach ($recoupments as $r) {
            $status = $r['hasReprocessed'] ? 'Reprocessed' : 'Pending Reprocess';
            $output .= '"' . str_replace('"', '""', $r['patientName']) . '",';
            $output .= $r['encounter'] . ',';
            $output .= $r['encounterDate'] . ',';
            $output .= '"' . str_replace('"', '""', $r['payerName']) . '",';
            $output .= '"' . str_replace('"', '""', $r['code']) . '",';
            $output .= $r['originalTotal'] . ',';
            $output .= $r['recoupAmount'] . ',';
            $output .= $r['recoupDate'] . ',';
            $output .= '"' . str_replace('"', '""', $r['recoupReference']) . '",';
            $output .= $r['reprocessedTotal'] . ',';
            $output .= $r['netImpact'] . ',';
            $output .= $r['currentBalance'] . ',';
            $output .= $status . "\n";
        }
        return $output;
    }
}
