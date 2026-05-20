<?php

/**
 * Service for the RCM Dashboard KPIs.
 *
 * Pulls metrics from OpenEMR billing tables and ClaimRev tracking tables
 * to power the home page dashboard.
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
 * @phpstan-type ClaimMetrics array{inFlight: int, pendingEras: int, rejected: int, cleanClaimRate: float|int}
 * @phpstan-type ArMetrics array{totalAr: float, avgDaysInAr: float, over90: float}
 * @phpstan-type DenialReason array{reason: string, count: int}
 * @phpstan-type DenialMetrics array{denialRate: float|int, totalDenied: int, totalProcessed: int, topReasons: list<DenialReason>}
 * @phpstan-type CollectionMetrics array{thisMonth: float, lastMonth: float, thisQuarter: float}
 * @phpstan-type PatientArMetrics array{totalPatientAr: float, encountersWithBalance: int, neverSentStatements: int}
 * @phpstan-type Kpis array{claims: ClaimMetrics, ar: ArMetrics, denials: DenialMetrics, collections: CollectionMetrics, patientAr: PatientArMetrics}
 */
class DashboardService
{
    /**
     * Get all KPI metrics for the dashboard.
     *
     * @return Kpis
     */
    public static function getKpis(): array
    {
        return [
            'claims' => self::getClaimMetrics(),
            'ar' => self::getArMetrics(),
            'denials' => self::getDenialMetrics(),
            'collections' => self::getCollectionMetrics(),
            'patientAr' => self::getPatientArMetrics(),
        ];
    }

    /**
     * Claim pipeline metrics.
     *
     * @return array{inFlight: int, pendingEras: int, rejected: int, cleanClaimRate: float}
     */
    private static function getClaimMetrics(): array
    {
        // Claims in flight: billed (status 2 or 6) in last 180 days
        $inFlight = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(DISTINCT CONCAT(c.patient_id, '-', c.encounter_id)) AS cnt " .
            "FROM claims c " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS mv FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = c.patient_id AND cv.encounter_id = c.encounter_id AND cv.mv = c.version " .
            "JOIN form_encounter fe ON fe.pid = c.patient_id AND fe.encounter = c.encounter_id " .
            "WHERE c.status IN (2, 6) AND fe.date >= DATE_SUB(NOW(), INTERVAL 180 DAY)",
            'cnt',
            []
        ));

        // Pending ERAs: claims with ERA received but no payment posted (from tracking table)
        $pendingEras = 0;
        $hasTrackingTable = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'mod_claimrev_claims'",
            'cnt',
            []
        ));
        if ($hasTrackingTable > 0) {
            $pendingEras = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
                "SELECT COUNT(*) AS cnt FROM mod_claimrev_claims " .
                "WHERE era_classification IS NOT NULL AND era_classification != '' AND ar_session_id IS NULL",
                'cnt',
                []
            ));
        }

        // Rejected in last 90 days
        $rejected = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(DISTINCT CONCAT(c.patient_id, '-', c.encounter_id)) AS cnt " .
            "FROM claims c " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS mv FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = c.patient_id AND cv.encounter_id = c.encounter_id AND cv.mv = c.version " .
            "JOIN form_encounter fe ON fe.pid = c.patient_id AND fe.encounter = c.encounter_id " .
            "WHERE c.status = 7 AND fe.date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            'cnt',
            []
        ));

        // Clean claim rate: (billed - rejected) / billed in last 90 days
        $totalBilled90 = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(DISTINCT CONCAT(c.patient_id, '-', c.encounter_id)) AS cnt " .
            "FROM claims c " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS mv FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = c.patient_id AND cv.encounter_id = c.encounter_id AND cv.mv = c.version " .
            "JOIN form_encounter fe ON fe.pid = c.patient_id AND fe.encounter = c.encounter_id " .
            "WHERE c.status IN (2, 6, 7) AND fe.date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            'cnt',
            []
        ));
        $cleanRate = $totalBilled90 > 0 ? round((($totalBilled90 - $rejected) / $totalBilled90) * 100, 1) : 0;

        return [
            'inFlight' => $inFlight,
            'pendingEras' => $pendingEras,
            'rejected' => $rejected,
            'cleanClaimRate' => $cleanRate,
        ];
    }

    /**
     * Accounts receivable metrics.
     *
     * @return array{totalAr: float, avgDaysInAr: float, over90: float}
     */
    private static function getArMetrics(): array
    {
        // Total AR and aging from encounters with balances (insurance responded)
        $rows = QueryUtils::fetchRecords(
            "SELECT " .
            "SUM(balance) AS total_ar, " .
            "AVG(age_days) AS avg_days, " .
            "SUM(CASE WHEN age_days > 90 THEN balance ELSE 0 END) AS over_90 " .
            "FROM (" .
            "SELECT fe.pid, fe.encounter, " .
            "DATEDIFF(NOW(), fe.date) AS age_days, " .
            "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            " + COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            " - COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            " - COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0)" .
            ") AS balance " .
            "FROM form_encounter fe " .
            "WHERE fe.last_level_closed >= 0 AND fe.date >= DATE_SUB(NOW(), INTERVAL 365 DAY) " .
            "GROUP BY fe.pid, fe.encounter " .
            "HAVING balance > 0.005" .
            ") AS sub",
            []
        );

        $r = $rows[0] ?? [];
        return [
            'totalAr' => round(TypeCoerce::asFloat($r['total_ar'] ?? 0), 2),
            'avgDaysInAr' => round(TypeCoerce::asFloat($r['avg_days'] ?? 0), 0),
            'over90' => round(TypeCoerce::asFloat($r['over_90'] ?? 0), 2),
        ];
    }

    /**
     * Denial metrics from ar_activity adjustment reasons.
     *
     * @return array{denialRate: float, totalDenied: int, totalProcessed: int, topReasons: list<array{reason: string, count: int}>}
     */
    private static function getDenialMetrics(): array
    {
        // Denial rate from claims table in last 90 days
        $denied90 = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(DISTINCT CONCAT(c.patient_id, '-', c.encounter_id)) AS cnt " .
            "FROM claims c " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS mv FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = c.patient_id AND cv.encounter_id = c.encounter_id AND cv.mv = c.version " .
            "JOIN form_encounter fe ON fe.pid = c.patient_id AND fe.encounter = c.encounter_id " .
            "WHERE c.status = 7 AND fe.date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            'cnt',
            []
        ));

        $processed90 = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(DISTINCT CONCAT(c.patient_id, '-', c.encounter_id)) AS cnt " .
            "FROM claims c " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS mv FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = c.patient_id AND cv.encounter_id = c.encounter_id AND cv.mv = c.version " .
            "JOIN form_encounter fe ON fe.pid = c.patient_id AND fe.encounter = c.encounter_id " .
            "WHERE c.status IN (2, 6, 7) AND fe.date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            'cnt',
            []
        ));

        $denialRate = $processed90 > 0 ? round(($denied90 / $processed90) * 100, 1) : 0;

        // Top denial/adjustment reasons from ar_activity (CO group codes)
        $topReasons = QueryUtils::fetchRecords(
            "SELECT a.memo AS reason, COUNT(*) AS cnt " .
            "FROM ar_activity a " .
            "WHERE a.deleted IS NULL AND a.adj_amount != 0 AND a.memo != '' " .
            "AND a.memo LIKE 'Adjust code %' " .
            "AND a.post_time >= DATE_SUB(NOW(), INTERVAL 90 DAY) " .
            "GROUP BY a.memo " .
            "ORDER BY cnt DESC " .
            "LIMIT 5",
            []
        );

        return [
            'denialRate' => $denialRate,
            'totalDenied' => $denied90,
            'totalProcessed' => $processed90,
            'topReasons' => array_map(fn(array $r): array => ['reason' => TypeCoerce::asString($r['reason'] ?? ''), 'count' => TypeCoerce::asInt($r['cnt'] ?? 0)], $topReasons),
        ];
    }

    /**
     * Collections metrics — payments received.
     *
     * @return array{thisMonth: float, lastMonth: float, thisQuarter: float}
     */
    private static function getCollectionMetrics(): array
    {
        $thisMonth = TypeCoerce::asFloat(QueryUtils::fetchSingleValue(
            "SELECT COALESCE(SUM(a.pay_amount), 0) AS total " .
            "FROM ar_activity a WHERE a.deleted IS NULL AND a.pay_amount > 0 " .
            "AND a.post_time >= DATE_FORMAT(NOW(), '%Y-%m-01')",
            'total',
            []
        ));

        $lastMonth = TypeCoerce::asFloat(QueryUtils::fetchSingleValue(
            "SELECT COALESCE(SUM(a.pay_amount), 0) AS total " .
            "FROM ar_activity a WHERE a.deleted IS NULL AND a.pay_amount > 0 " .
            "AND a.post_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') " .
            "AND a.post_time < DATE_FORMAT(NOW(), '%Y-%m-01')",
            'total',
            []
        ));

        $thisQuarter = TypeCoerce::asFloat(QueryUtils::fetchSingleValue(
            "SELECT COALESCE(SUM(a.pay_amount), 0) AS total " .
            "FROM ar_activity a WHERE a.deleted IS NULL AND a.pay_amount > 0 " .
            "AND a.post_time >= DATE_FORMAT(MAKEDATE(YEAR(NOW()), 1) + INTERVAL (QUARTER(NOW()) - 1) * 3 MONTH, '%Y-%m-01')",
            'total',
            []
        ));

        return [
            'thisMonth' => round($thisMonth, 2),
            'lastMonth' => round($lastMonth, 2),
            'thisQuarter' => round($thisQuarter, 2),
        ];
    }

    /**
     * Patient AR summary.
     *
     * @return array{totalPatientAr: float, encountersWithBalance: int, neverSentStatements: int}
     */
    private static function getPatientArMetrics(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT COUNT(*) AS enc_count, SUM(balance) AS total_bal " .
            "FROM (" .
            "SELECT fe.pid, fe.encounter, " .
            "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            " + COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            " - COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            " - COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0)" .
            ") AS balance " .
            "FROM form_encounter fe " .
            "WHERE fe.last_level_closed >= 1 " .
            "GROUP BY fe.pid, fe.encounter " .
            "HAVING balance > 0.005" .
            ") AS sub",
            []
        );

        $r = $rows[0] ?? [];
        $encCount = TypeCoerce::asInt($r['enc_count'] ?? 0);
        $totalBal = round(TypeCoerce::asFloat($r['total_bal'] ?? 0), 2);

        // Never sent statements
        $neverSent = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt FROM (" .
            "SELECT fe.pid, fe.encounter, " .
            "(COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1), 0) " .
            " + COALESCE((SELECT SUM(ds.fee) FROM drug_sales ds WHERE ds.pid = fe.pid AND ds.encounter = fe.encounter), 0) " .
            " - COALESCE((SELECT SUM(a.pay_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0) " .
            " - COALESCE((SELECT SUM(a.adj_amount) FROM ar_activity a WHERE a.pid = fe.pid AND a.encounter = fe.encounter AND a.deleted IS NULL), 0)" .
            ") AS balance " .
            "FROM form_encounter fe " .
            "WHERE fe.last_level_closed >= 1 AND fe.stmt_count = 0 " .
            "GROUP BY fe.pid, fe.encounter " .
            "HAVING balance > 0.005" .
            ") AS sub",
            'cnt',
            []
        ));

        return [
            'totalPatientAr' => $totalBal,
            'encountersWithBalance' => $encCount,
            'neverSentStatements' => $neverSent,
        ];
    }
}
