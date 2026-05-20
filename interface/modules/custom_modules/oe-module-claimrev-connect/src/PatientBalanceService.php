<?php

/**
 * Service for managing the Patient Balance queue.
 *
 * Surfaces encounters with outstanding patient responsibility after
 * insurance has responded (last_level_closed >= 1), shows ERA-derived
 * PR breakdown, and tracks statement history.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Common\Database\QueryUtils;

/**
 * @phpstan-type PatientBalanceRow array{
 *     pid: int,
 *     encounter: int,
 *     encounterDate: string,
 *     patientName: string,
 *     patientDob: string,
 *     payerName: string,
 *     payerNumber: string,
 *     totalCharges: float,
 *     insPaid: float,
 *     balance: float,
 *     stmtCount: int,
 *     lastStmtDate: string,
 *     inCollection: bool
 * }
 * @phpstan-type PatientBalanceStats array{totalWithBalance: int, totalAmount: float, neverSent: int, sent1x: int, sent2plus: int, inCollection: int}
 */
class PatientBalanceService
{
    /**
     * Check if the mod_claimrev_patient_statements table exists.
     */
    private static function statementsTableExists(): bool
    {
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt FROM information_schema.tables " .
            "WHERE table_schema = DATABASE() AND table_name = 'mod_claimrev_patient_statements'",
            'cnt',
            []
        );
        return TypeCoerce::asInt($count) > 0;
    }

    /**
     * Get encounters with outstanding patient balances.
     *
     * Uses the same balance formula as OpenEMR's collections report:
     * charges + drug_sales - payments - adjustments.
     * Only shows encounters where last_level_closed >= 1 (insurance responded).
     *
     * @param array{dateStart?: string, dateEnd?: string, patientName?: string, payerName?: string, minAmount?: string, stmtFilter?: string, pageIndex?: int} $filters
     * @return array{encounters: list<PatientBalanceRow>, totalRecords: int}
     */
    public static function getPatientBalanceQueue(array $filters): array
    {
        $pageIndex = (int) ($filters['pageIndex'] ?? 0);
        $pageSize = 50;
        $offset = $pageIndex * $pageSize;
        $minAmountRaw = $filters['minAmount'] ?? '';
        $minAmount = $minAmountRaw !== '' ? (float) $minAmountRaw : 0.01;
        $stmtFilter = $filters['stmtFilter'] ?? '';
        $hasStmtTable = self::statementsTableExists();

        $where = ["fe.last_level_closed >= 1"];
        $params = [];

        $dateStart = $filters['dateStart'] ?? '';
        if ($dateStart !== '') {
            $where[] = "fe.date >= ?";
            $params[] = $dateStart . ' 00:00:00';
        }
        $dateEnd = $filters['dateEnd'] ?? '';
        if ($dateEnd !== '') {
            $where[] = "fe.date <= ?";
            $params[] = $dateEnd . ' 23:59:59';
        }
        $patientName = $filters['patientName'] ?? '';
        if ($patientName !== '') {
            $where[] = "(p.lname LIKE ? OR p.fname LIKE ?)";
            $namePat = '%' . $patientName . '%';
            $params[] = $namePat;
            $params[] = $namePat;
        }
        $payerName = $filters['payerName'] ?? '';
        if ($payerName !== '') {
            // Use a subquery for payer filter to avoid join duplication
            $where[] = "EXISTS (SELECT 1 FROM insurance_data id2 JOIN insurance_companies ic2 ON ic2.id = id2.provider WHERE id2.pid = fe.pid AND id2.type = 'primary' AND ic2.name LIKE ?)";
            $params[] = '%' . $payerName . '%';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // All correlated subqueries — no JOINs that can cause duplication
        $balanceSql = "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            "+ COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            "- COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            "- COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0))";

        $chargesSql = "COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0)";
        $insPaidSql = "COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL AND a.payer_type > 0), 0)";

        // Payer name via correlated subquery (gets the latest primary insurance)
        $payerNameSql = "(SELECT ic.name FROM insurance_data id JOIN insurance_companies ic ON ic.id = id.provider WHERE id.pid = fe.pid AND id.type = 'primary' ORDER BY id.date DESC LIMIT 1)";
        $payerNumberSql = "(SELECT ic.cms_id FROM insurance_data id JOIN insurance_companies ic ON ic.id = id.provider WHERE id.pid = fe.pid AND id.type = 'primary' ORDER BY id.date DESC LIMIT 1)";

        // Statement tracking — use 0 if table doesn't exist
        $stmtSentSql = $hasStmtTable
            ? "(SELECT COUNT(*) FROM mod_claimrev_patient_statements ps WHERE ps.pid = fe.pid AND ps.encounter = fe.encounter AND ps.status != 'void')"
            : "0";
        $lastStmtSql = $hasStmtTable
            ? "(SELECT MAX(ps.statement_date) FROM mod_claimrev_patient_statements ps WHERE ps.pid = fe.pid AND ps.encounter = fe.encounter AND ps.status != 'void')"
            : "NULL";

        // Build the inner query that computes balance per encounter
        $innerSql = "SELECT fe.pid, fe.encounter, fe.date AS encounter_date, " .
            "fe.stmt_count AS oe_stmt_count, fe.last_stmt_date, fe.in_collection, " .
            "p.fname, p.lname, p.DOB, " .
            "$payerNameSql AS payer_name, " .
            "$payerNumberSql AS payer_number, " .
            "$balanceSql AS balance, " .
            "$chargesSql AS total_charges, " .
            "$insPaidSql AS ins_paid, " .
            "$stmtSentSql AS stmt_sent, " .
            "$lastStmtSql AS last_stmt_date_cr " .
            "FROM form_encounter fe " .
            "JOIN patient_data p ON p.pid = fe.pid " .
            $whereClause;

        // Wrap in outer query for HAVING on the computed balance
        $outerWhere = ["sub.balance > ?"];
        $outerParams = [$minAmount];

        if ($stmtFilter === 'never_sent') {
            $outerWhere[] = "sub.stmt_sent = 0 AND sub.oe_stmt_count = 0";
        } elseif ($stmtFilter === 'sent_1x') {
            $outerWhere[] = "(GREATEST(sub.stmt_sent, sub.oe_stmt_count) = 1)";
        } elseif ($stmtFilter === 'sent_2plus') {
            $outerWhere[] = "(GREATEST(sub.stmt_sent, sub.oe_stmt_count) >= 2)";
        } elseif ($stmtFilter === 'in_collection') {
            $outerWhere[] = "sub.in_collection = 1";
        }

        $outerWhereClause = 'WHERE ' . implode(' AND ', $outerWhere);
        $allParams = array_merge($params, $outerParams);

        // Count
        $countSql = "SELECT COUNT(*) AS cnt FROM ($innerSql) AS sub $outerWhereClause";
        $totalRecords = TypeCoerce::asInt(QueryUtils::fetchSingleValue($countSql, 'cnt', $allParams));

        // Data
        $dataSql = "SELECT sub.* FROM ($innerSql) AS sub $outerWhereClause " .
            "ORDER BY sub.balance DESC LIMIT {$pageSize} OFFSET {$offset}";
        $rows = QueryUtils::fetchRecords($dataSql, $allParams);

        $encounters = [];
        foreach ($rows as $row) {
            $stmtCount = max(TypeCoerce::asInt($row['stmt_sent'] ?? 0), TypeCoerce::asInt($row['oe_stmt_count'] ?? 0));
            $lastStmtCr = TypeCoerce::asString($row['last_stmt_date_cr'] ?? '');
            $lastStmtOe = TypeCoerce::asString($row['last_stmt_date'] ?? '');
            $lastStmt = $lastStmtCr !== '' ? $lastStmtCr : $lastStmtOe;

            $encounters[] = [
                'pid' => TypeCoerce::asInt($row['pid'] ?? 0),
                'encounter' => TypeCoerce::asInt($row['encounter'] ?? 0),
                'encounterDate' => substr(TypeCoerce::asString($row['encounter_date'] ?? ''), 0, 10),
                'patientName' => TypeCoerce::asString($row['lname'] ?? '') . ', ' . TypeCoerce::asString($row['fname'] ?? ''),
                'patientDob' => substr(TypeCoerce::asString($row['DOB'] ?? ''), 0, 10),
                'payerName' => TypeCoerce::asString($row['payer_name'] ?? ''),
                'payerNumber' => TypeCoerce::asString($row['payer_number'] ?? ''),
                'totalCharges' => TypeCoerce::asFloat($row['total_charges'] ?? 0),
                'insPaid' => TypeCoerce::asFloat($row['ins_paid'] ?? 0),
                'balance' => round(TypeCoerce::asFloat($row['balance'] ?? 0), 2),
                'stmtCount' => $stmtCount,
                'lastStmtDate' => $lastStmt,
                'inCollection' => TypeCoerce::asBool($row['in_collection'] ?? false),
            ];
        }

        return ['encounters' => $encounters, 'totalRecords' => $totalRecords];
    }

    /**
     * Get per-code balance detail for an encounter using InvoiceSummary.
     *
     * @return array{codes: list<array<string, mixed>>, prMemos: array<string, float>, totalBalance: float}
     */
    public static function getBalanceDetail(int $pid, int $encounter): array
    {
        $invoiceCodes = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);
        $prMemos = self::parsePrMemos($pid, $encounter);

        $codes = [];
        $totalBalance = 0.0;
        if (!is_array($invoiceCodes)) {
            return [
                'codes' => $codes,
                'prMemos' => $prMemos,
                'totalBalance' => round($totalBalance, 2),
            ];
        }
        foreach ($invoiceCodes as $code => $cdata) {
            if (!is_array($cdata)) {
                continue;
            }
            $bal = round(TypeCoerce::asFloat($cdata['bal'] ?? 0), 2);
            $totalBalance += $bal;

            $details = [];
            $dtlList = $cdata['dtl'] ?? null;
            if (is_array($dtlList) && $dtlList !== []) {
                foreach ($dtlList as $key => $dtl) {
                    if (!is_array($dtl)) {
                        continue;
                    }
                    $details[] = [
                        'date' => trim(substr((string) $key, 0, 10)),
                        'payment' => TypeCoerce::asFloat($dtl['pmt'] ?? 0),
                        'adjustment' => TypeCoerce::asFloat($dtl['chg'] ?? 0),
                        'reason' => TypeCoerce::asString($dtl['rsn'] ?? ''),
                        'source' => TypeCoerce::asString($dtl['src'] ?? ''),
                        'payerLevel' => TypeCoerce::asInt($dtl['plv'] ?? 0),
                    ];
                }
            }

            $codes[] = [
                'code' => $code,
                'codeType' => TypeCoerce::asString($cdata['code_type'] ?? ''),
                'codeText' => TypeCoerce::asString($cdata['code_text'] ?? ''),
                'charge' => round(TypeCoerce::asFloat($cdata['chg'] ?? 0), 2),
                'balance' => $bal,
                'adjustment' => round(TypeCoerce::asFloat($cdata['adj'] ?? 0), 2),
                'details' => $details,
            ];
        }

        return [
            'codes' => $codes,
            'prMemos' => $prMemos,
            'totalBalance' => round($totalBalance, 2),
        ];
    }

    /**
     * Parse patient responsibility memos from ar_activity.
     *
     * Looks for memos matching "Ins\d+ (dedbl|coins|copay|ptresp): \d+.\d+"
     * posted by the ERA posting process.
     *
     * @return array{deductible: float, coinsurance: float, copay: float, ptresp: float}
     */
    public static function parsePrMemos(int $pid, int $encounter): array
    {
        $result = [
            'deductible' => 0.0,
            'coinsurance' => 0.0,
            'copay' => 0.0,
            'ptresp' => 0.0,
        ];

        $rows = QueryUtils::fetchRecords(
            "SELECT memo FROM ar_activity " .
            "WHERE pid = ? AND encounter = ? AND deleted IS NULL " .
            "AND adj_amount = 0 AND pay_amount = 0 AND memo IS NOT NULL",
            [$pid, $encounter]
        );

        foreach ($rows as $row) {
            $memo = TypeCoerce::asString($row['memo'] ?? '');
            if (preg_match('/Ins\d+\s+(dedbl|coins|copay|ptresp):\s*([\d.]+)/', $memo, $m)) {
                $type = $m[1];
                $amount = (float) $m[2];
                match ($type) {
                    'dedbl' => $result['deductible'] += $amount,
                    'coins' => $result['coinsurance'] += $amount,
                    'copay' => $result['copay'] += $amount,
                    'ptresp' => $result['ptresp'] += $amount,
                };
            }
        }

        return $result;
    }

    /**
     * Log a statement to the tracking table.
     */
    public static function logStatement(
        int $pid,
        int $encounter,
        string $method,
        float $amount,
        string $notes = '',
        ?string $claimrevStatementId = null,
    ): int {
        if (!self::statementsTableExists()) {
            return 0;
        }

        $user = $_SESSION['authUser'] ?? 'system';
        $sql = "INSERT INTO mod_claimrev_patient_statements " .
            "(pid, encounter, statement_date, statement_method, amount_due, status, claimrev_statement_id, notes, created_by, created_date) " .
            "VALUES (?, ?, CURDATE(), ?, ?, 'generated', ?, ?, ?, NOW())";
        QueryUtils::sqlInsert($sql, [
            $pid,
            $encounter,
            $method,
            $amount,
            $claimrevStatementId,
            $notes,
            $user,
        ]);

        return TypeCoerce::asInt(QueryUtils::fetchSingleValue("SELECT LAST_INSERT_ID() AS id", 'id', []));
    }

    /**
     * Get statement history for an encounter.
     *
     * @return list<array<string, mixed>>
     */
    public static function getStatementHistory(int $pid, int $encounter): array
    {
        if (!self::statementsTableExists()) {
            return [];
        }

        $rows = QueryUtils::fetchRecords(
            "SELECT id, statement_date, statement_method, amount_due, status, " .
            "claimrev_statement_id, notes, created_by, created_date " .
            "FROM mod_claimrev_patient_statements " .
            "WHERE pid = ? AND encounter = ? " .
            "ORDER BY created_date DESC",
            [$pid, $encounter]
        );

        $out = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Get aggregate stats for the queue summary cards.
     *
     * @param array<string, mixed> $filters Same filters as getPatientBalanceQueue
     * @param array{dateStart?: string, dateEnd?: string, patientName?: string, payerName?: string, minAmount?: string, stmtFilter?: string} $filters
     * @return PatientBalanceStats
     */
    public static function getQueueStats(array $filters): array
    {
        $hasStmtTable = self::statementsTableExists();

        $where = ["fe.last_level_closed >= 1"];
        $params = [];
        $minAmountRaw = $filters['minAmount'] ?? '';
        $minAmount = $minAmountRaw !== '' ? (float) $minAmountRaw : 0.01;

        $dateStart = $filters['dateStart'] ?? '';
        if ($dateStart !== '') {
            $where[] = "fe.date >= ?";
            $params[] = $dateStart . ' 00:00:00';
        }
        $dateEnd = $filters['dateEnd'] ?? '';
        if ($dateEnd !== '') {
            $where[] = "fe.date <= ?";
            $params[] = $dateEnd . ' 23:59:59';
        }
        $patientName = $filters['patientName'] ?? '';
        if ($patientName !== '') {
            $where[] = "(p.lname LIKE ? OR p.fname LIKE ?)";
            $namePat = '%' . $patientName . '%';
            $params[] = $namePat;
            $params[] = $namePat;
        }
        $payerName = $filters['payerName'] ?? '';
        if ($payerName !== '') {
            $where[] = "EXISTS (SELECT 1 FROM insurance_data id2 JOIN insurance_companies ic2 ON ic2.id = id2.provider WHERE id2.pid = fe.pid AND id2.type = 'primary' AND ic2.name LIKE ?)";
            $params[] = '%' . $payerName . '%';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $balanceSql = "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            "+ COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            "- COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            "- COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0))";

        $stmtSentSql = $hasStmtTable
            ? "(SELECT COUNT(*) FROM mod_claimrev_patient_statements ps WHERE ps.pid = fe.pid AND ps.encounter = fe.encounter AND ps.status != 'void')"
            : "0";

        $innerSql = "SELECT fe.pid, fe.encounter, $balanceSql AS balance, " .
            "$stmtSentSql AS stmt_sent, fe.stmt_count AS oe_stmt_count, fe.in_collection " .
            "FROM form_encounter fe " .
            "JOIN patient_data p ON p.pid = fe.pid " .
            $whereClause;

        $sql = "SELECT " .
            "COUNT(*) AS total_with_balance, " .
            "COALESCE(SUM(sub.balance), 0) AS total_amount, " .
            "SUM(CASE WHEN sub.stmt_sent = 0 AND sub.oe_stmt_count = 0 THEN 1 ELSE 0 END) AS never_sent, " .
            "SUM(CASE WHEN GREATEST(sub.stmt_sent, sub.oe_stmt_count) = 1 THEN 1 ELSE 0 END) AS sent_1x, " .
            "SUM(CASE WHEN GREATEST(sub.stmt_sent, sub.oe_stmt_count) >= 2 THEN 1 ELSE 0 END) AS sent_2plus, " .
            "SUM(CASE WHEN sub.in_collection = 1 THEN 1 ELSE 0 END) AS in_collection " .
            "FROM ($innerSql) AS sub " .
            "WHERE sub.balance > ?";

        $row = QueryUtils::fetchRecords($sql, array_merge($params, [$minAmount]));
        $r = $row[0] ?? [];

        return [
            'totalWithBalance' => TypeCoerce::asInt($r['total_with_balance'] ?? 0),
            'totalAmount' => round(TypeCoerce::asFloat($r['total_amount'] ?? 0), 2),
            'neverSent' => TypeCoerce::asInt($r['never_sent'] ?? 0),
            'sent1x' => TypeCoerce::asInt($r['sent_1x'] ?? 0),
            'sent2plus' => TypeCoerce::asInt($r['sent_2plus'] ?? 0),
            'inCollection' => TypeCoerce::asInt($r['in_collection'] ?? 0),
        ];
    }
}
