<?php

/**
 * Service for tracking claim lifecycle events in local tables.
 *
 * Provides a local mirror of ClaimRev claim status with full event history,
 * work queue for billers, and real-time status check support.
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
 * @phpstan-type ClaimWorkRow array{
 *     pid: int,
 *     encounter: int,
 *     payerType: int,
 *     pcn: string,
 *     encounterDate: string,
 *     patientName: string,
 *     patientDob: string,
 *     payerName: string,
 *     payerNumber: string,
 *     totalCharges: float,
 *     billTime: string,
 *     oeStatus: int,
 *     oeStatusLabel: string,
 *     trackingId: ?int,
 *     crObjectId: string,
 *     crStatusId: int,
 *     crStatusName: string,
 *     payerAcceptanceStatusId: int,
 *     payerAcceptanceName: string,
 *     eraClassification: string,
 *     payerPaidAmount: float,
 *     isWorked: bool,
 *     arSessionId: ?int,
 *     lastStatusCheck: string,
 *     lastSynced: string
 * }
 * @phpstan-type ClaimDashboardStats array{total: int, needingAttention: int, rejected: int, denied: int, stale: int, paid: int, paidNotPosted: int}
 */
class ClaimTrackingService
{
    // Event types
    public const EVENT_SUBMITTED = 'submitted';
    public const EVENT_REJECTED = 'rejected';
    public const EVENT_ACCEPTED = 'accepted';
    public const EVENT_DENIED = 'denied';
    public const EVENT_STATUS_CHECK_276 = 'status_check_276';
    public const EVENT_ERA_RECEIVED = 'era_received';
    public const EVENT_PAYMENT_POSTED = 'payment_posted';
    public const EVENT_REQUEUED = 'requeued';
    public const EVENT_CORRECTED = 'corrected';
    public const EVENT_MANUAL_NOTE = 'manual_note';
    public const EVENT_CLAIMREV_SYNC = 'claimrev_sync';

    // Sources
    public const SOURCE_CLAIMREV = 'claimrev';
    public const SOURCE_PAYER_277 = 'payer_277';
    public const SOURCE_USER = 'user';
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_ERA = 'era';

    /** @var int Days without ERA before a billed claim is considered stale */
    private const STALE_THRESHOLD_DAYS = 45;

    /**
     * Parse a patient control number into pid and encounter.
     *
     * @return array{pid: int, encounter: int}|null
     */
    public static function parsePcn(string $pcn): ?array
    {
        $parts = preg_split('/[\s\-]/', $pcn);
        if (!is_array($parts) || count($parts) < 2) {
            return null;
        }

        $pid = (int) $parts[0];
        $encounter = (int) $parts[1];

        if ($pid <= 0 || $encounter <= 0) {
            return null;
        }

        return ['pid' => $pid, 'encounter' => $encounter];
    }

    /**
     * Create or update a claim record from ClaimRev data.
     *
     * @param array<string, mixed> $crData Row from ClaimRev searchClaims result
     * @return int The mod_claimrev_claims.id
     */
    public static function upsertClaimRecord(
        int $pid,
        int $encounter,
        int $payerType,
        array $crData
    ): int {
        $objectId = TypeCoerce::asString($crData['objectId'] ?? '');
        $statusId = TypeCoerce::asNullableInt($crData['statusId'] ?? null);
        $statusName = TypeCoerce::asString($crData['statusName'] ?? '');
        $payerAccId = TypeCoerce::asNullableInt($crData['payerAcceptanceStatusId'] ?? null);
        $payerAccName = TypeCoerce::asString($crData['payerAcceptanceStatusName'] ?? '');
        $eraCls = TypeCoerce::asString($crData['eraClassification'] ?? '');
        $paidAmtRaw = $crData['payerPaidAmount'] ?? null;
        $paidAmt = $paidAmtRaw !== null ? TypeCoerce::asFloat($paidAmtRaw) : null;
        $isWorked = TypeCoerce::asBool($crData['isWorked'] ?? false) ? 1 : 0;

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO mod_claimrev_claims " .
            "(pid, encounter, payer_type, claimrev_object_id, claimrev_status_id, claimrev_status_name, " .
            " payer_acceptance_status_id, payer_acceptance_status_name, era_classification, " .
            " payer_paid_amount, is_worked, last_synced) " .
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()) " .
            "ON DUPLICATE KEY UPDATE " .
            " claimrev_object_id = VALUES(claimrev_object_id), " .
            " claimrev_status_id = VALUES(claimrev_status_id), " .
            " claimrev_status_name = VALUES(claimrev_status_name), " .
            " payer_acceptance_status_id = VALUES(payer_acceptance_status_id), " .
            " payer_acceptance_status_name = VALUES(payer_acceptance_status_name), " .
            " era_classification = VALUES(era_classification), " .
            " payer_paid_amount = VALUES(payer_paid_amount), " .
            " is_worked = VALUES(is_worked), " .
            " last_synced = NOW()",
            [$pid, $encounter, $payerType, $objectId, $statusId, $statusName,
             $payerAccId, $payerAccName, $eraCls, $paidAmt, $isWorked]
        );

        $row = QueryUtils::querySingleRow(
            "SELECT id FROM mod_claimrev_claims WHERE pid = ? AND encounter = ? AND payer_type = ?",
            [$pid, $encounter, $payerType]
        );

        return TypeCoerce::asInt($row['id'] ?? 0);
    }

    /**
     * Update ar_session_id after payment posting.
     */
    public static function linkPaymentSession(
        int $pid,
        int $encounter,
        int $payerType,
        int $arSessionId
    ): void {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO mod_claimrev_claims (pid, encounter, payer_type, ar_session_id) " .
            "VALUES (?, ?, ?, ?) " .
            "ON DUPLICATE KEY UPDATE ar_session_id = VALUES(ar_session_id)",
            [$pid, $encounter, $payerType, $arSessionId]
        );
    }

    /**
     * Log an event to the claim events table.
     *
     * @return int The inserted event ID
     */
    public static function logEvent(
        int $pid,
        int $encounter,
        int $payerType,
        string $eventType,
        string $source,
        ?string $statusCode = null,
        ?string $statusDescription = null,
        ?string $detailText = null,
        ?float $amount = null,
        ?string $createdBy = null
    ): int {
        if ($createdBy === null) {
            $createdBy = TypeCoerce::asString($_SESSION['authUser'] ?? 'system', 'system');
        }

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO mod_claimrev_claim_events " .
            "(pid, encounter, payer_type, event_type, source, status_code, status_description, " .
            " detail_text, amount, created_by) " .
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$pid, $encounter, $payerType, $eventType, $source,
             $statusCode, $statusDescription, $detailText, $amount, $createdBy]
        );

        $row = QueryUtils::querySingleRow("SELECT LAST_INSERT_ID() AS id");
        return TypeCoerce::asInt($row['id'] ?? 0);
    }

    /**
     * Get current claim record.
     *
     * @return array<string, mixed>|null
     */
    public static function getClaimRecord(int $pid, int $encounter, int $payerType): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT * FROM mod_claimrev_claims WHERE pid = ? AND encounter = ? AND payer_type = ?",
            [$pid, $encounter, $payerType]
        );

        if (!is_array($row) || $row === []) {
            return null;
        }
        /** @var array<string, mixed> $row */
        return $row;
    }

    /**
     * Get the full timeline of events for a claim.
     *
     * @return list<array<string, mixed>> Ordered by created_date DESC
     */
    public static function getClaimTimeline(int $pid, int $encounter, int $payerType = 0): array
    {
        $params = [$pid, $encounter];
        $where = "pid = ? AND encounter = ?";

        if ($payerType > 0) {
            $where .= " AND payer_type = ?";
            $params[] = $payerType;
        }

        $rows = QueryUtils::fetchRecords(
            "SELECT * FROM mod_claimrev_claim_events WHERE {$where} ORDER BY created_date DESC, id DESC",
            $params
        );

        $out = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Get work queue: claims needing attention.
     *
     * @param array{statusFilter?: string, dateStart?: string, dateEnd?: string, patientLastName?: string, payerName?: string, pageIndex?: int} $filters
     * @return array{claims: list<ClaimWorkRow>, totalRecords: int}
     */
    public static function getWorkQueue(array $filters): array
    {
        $pageIndex = (int) ($filters['pageIndex'] ?? 0);
        $pageSize = 50;
        $offset = $pageIndex * $pageSize;
        $statusFilter = $filters['statusFilter'] ?? 'all';

        $where = [];
        $params = [];

        // Always join with OpenEMR claims to get billed encounters
        // Use latest version of each claim
        $baseJoin = "FROM form_encounter e " .
            "JOIN patient_data p ON p.pid = e.pid " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS max_version FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = e.pid AND cv.encounter_id = e.encounter " .
            "JOIN claims c ON c.patient_id = cv.patient_id AND c.encounter_id = cv.encounter_id AND c.version = cv.max_version " .
            "LEFT JOIN mod_claimrev_claims mc ON mc.pid = e.pid AND mc.encounter = e.encounter AND mc.payer_type = c.payer_type " .
            "LEFT JOIN insurance_data id ON id.pid = e.pid AND id.type = 'primary' AND id.date <= e.date " .
            "LEFT JOIN insurance_companies ic ON ic.id = id.provider ";

        // Must have been billed at minimum
        $where[] = "c.status >= 2";

        // Status-specific filters
        if ($statusFilter === 'rejected') {
            $where[] = "(mc.claimrev_status_id IN (10, 16, 17) OR mc.payer_acceptance_status_id = 3)";
        } elseif ($statusFilter === 'denied') {
            $where[] = "(mc.era_classification LIKE '%denied%' OR c.status = 7)";
        } elseif ($statusFilter === 'stale') {
            $where[] = "c.status = 2";
            $where[] = "(mc.era_classification IS NULL OR mc.era_classification = '')";
            $where[] = "c.bill_time < NOW() - INTERVAL " . self::STALE_THRESHOLD_DAYS . " DAY";
        } elseif ($statusFilter === 'paid_not_posted') {
            $where[] = "mc.era_classification LIKE '%paid%'";
            $where[] = "mc.ar_session_id IS NULL";
        } elseif ($statusFilter === 'unworked') {
            $where[] = "(mc.is_worked = 0 OR mc.is_worked IS NULL)";
            $where[] = "mc.id IS NOT NULL";
        }

        // Date filter
        $dateStart = $filters['dateStart'] ?? '';
        if ($dateStart !== '') {
            $where[] = "e.date >= ?";
            $params[] = $dateStart . ' 00:00:00';
        }
        $dateEnd = $filters['dateEnd'] ?? '';
        if ($dateEnd !== '') {
            $where[] = "e.date <= ?";
            $params[] = $dateEnd . ' 23:59:59';
        }

        // Patient filter
        $patientLastName = $filters['patientLastName'] ?? '';
        if ($patientLastName !== '') {
            $where[] = "p.lname LIKE ?";
            $params[] = '%' . $patientLastName . '%';
        }

        // Payer filter
        $payerName = $filters['payerName'] ?? '';
        if ($payerName !== '') {
            $where[] = "ic.name LIKE ?";
            $params[] = '%' . $payerName . '%';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Count
        $countSql = "SELECT COUNT(*) AS cnt " . $baseJoin . $whereClause;
        $totalRecords = TypeCoerce::asInt(QueryUtils::fetchSingleValue($countSql, 'cnt', $params));

        // Fetch
        $sql = "SELECT e.pid, e.encounter, e.date AS encounter_date, " .
            "p.fname, p.lname, p.DOB, " .
            "c.status AS oe_status, c.payer_type, c.bill_time, c.process_file, " .
            "ic.name AS payer_name, ic.cms_id AS payer_number, " .
            "COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = e.pid AND b.encounter = e.encounter AND b.activity = 1), 0) AS total_charges, " .
            "mc.id AS tracking_id, mc.claimrev_object_id, mc.claimrev_status_id, mc.claimrev_status_name, " .
            "mc.payer_acceptance_status_id, mc.payer_acceptance_status_name, " .
            "mc.era_classification, mc.payer_paid_amount, mc.is_worked, mc.ar_session_id, " .
            "mc.last_status_check_date, mc.last_synced " .
            $baseJoin . $whereClause . " " .
            "GROUP BY e.pid, e.encounter, c.payer_type " .
            "ORDER BY e.date DESC " .
            "LIMIT {$pageSize} OFFSET {$offset}";
        $rows = QueryUtils::fetchRecords($sql, $params);

        $oeLabels = [
            0 => 'Not Billed', 1 => 'Unbilled', 2 => 'Billed',
            3 => 'Processed', 6 => 'Crossover', 7 => 'Denied',
        ];

        $claims = [];
        foreach ($rows as $row) {
            $pid = TypeCoerce::asInt($row['pid'] ?? 0);
            $encounter = TypeCoerce::asInt($row['encounter'] ?? 0);
            $oeStatus = TypeCoerce::asInt($row['oe_status'] ?? 0);
            $trackingId = TypeCoerce::asNullableInt($row['tracking_id'] ?? null);
            $arSessionId = TypeCoerce::asNullableInt($row['ar_session_id'] ?? null);

            $claims[] = [
                'pid' => $pid,
                'encounter' => $encounter,
                'payerType' => TypeCoerce::asInt($row['payer_type'] ?? 0),
                'pcn' => $pid . '-' . $encounter,
                'encounterDate' => substr(TypeCoerce::asString($row['encounter_date'] ?? ''), 0, 10),
                'patientName' => TypeCoerce::asString($row['lname'] ?? '') . ', ' . TypeCoerce::asString($row['fname'] ?? ''),
                'patientDob' => substr(TypeCoerce::asString($row['DOB'] ?? ''), 0, 10),
                'payerName' => TypeCoerce::asString($row['payer_name'] ?? ''),
                'payerNumber' => TypeCoerce::asString($row['payer_number'] ?? ''),
                'totalCharges' => TypeCoerce::asFloat($row['total_charges'] ?? 0),
                'billTime' => TypeCoerce::asString($row['bill_time'] ?? ''),
                'oeStatus' => $oeStatus,
                'oeStatusLabel' => $oeLabels[$oeStatus] ?? 'Unknown (' . $oeStatus . ')',
                'trackingId' => ($trackingId !== null && $trackingId !== 0) ? $trackingId : null,
                'crObjectId' => TypeCoerce::asString($row['claimrev_object_id'] ?? ''),
                'crStatusId' => TypeCoerce::asInt($row['claimrev_status_id'] ?? 0),
                'crStatusName' => TypeCoerce::asString($row['claimrev_status_name'] ?? ''),
                'payerAcceptanceStatusId' => TypeCoerce::asInt($row['payer_acceptance_status_id'] ?? 0),
                'payerAcceptanceName' => TypeCoerce::asString($row['payer_acceptance_status_name'] ?? ''),
                'eraClassification' => TypeCoerce::asString($row['era_classification'] ?? ''),
                'payerPaidAmount' => TypeCoerce::asFloat($row['payer_paid_amount'] ?? 0),
                'isWorked' => TypeCoerce::asBool($row['is_worked'] ?? false),
                'arSessionId' => ($arSessionId !== null && $arSessionId !== 0) ? $arSessionId : null,
                'lastStatusCheck' => TypeCoerce::asString($row['last_status_check_date'] ?? ''),
                'lastSynced' => TypeCoerce::asString($row['last_synced'] ?? ''),
            ];
        }

        return ['claims' => $claims, 'totalRecords' => $totalRecords];
    }

    /**
     * Get summary statistics for the dashboard.
     *
     * @param array{dateStart?: string, dateEnd?: string} $filters
     * @return ClaimDashboardStats
     */
    public static function getDashboardStats(array $filters = []): array
    {
        $dateWhere = '';
        $params = [];

        $dateStart = $filters['dateStart'] ?? '';
        if ($dateStart !== '') {
            $dateWhere .= " AND e.date >= ?";
            $params[] = $dateStart . ' 00:00:00';
        }
        $dateEnd = $filters['dateEnd'] ?? '';
        if ($dateEnd !== '') {
            $dateWhere .= " AND e.date <= ?";
            $params[] = $dateEnd . ' 23:59:59';
        }

        $baseFrom = "FROM form_encounter e " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS max_version FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = e.pid AND cv.encounter_id = e.encounter " .
            "JOIN claims c ON c.patient_id = cv.patient_id AND c.encounter_id = cv.encounter_id AND c.version = cv.max_version " .
            "LEFT JOIN mod_claimrev_claims mc ON mc.pid = e.pid AND mc.encounter = e.encounter AND mc.payer_type = c.payer_type " .
            "WHERE c.status >= 2" . $dateWhere;

        $total = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom,
            'cnt',
            $params
        ));

        $rejected = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom . " AND (mc.claimrev_status_id IN (10, 16, 17) OR mc.payer_acceptance_status_id = 3)",
            'cnt',
            $params
        ));

        $denied = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom . " AND (mc.era_classification LIKE '%denied%' OR c.status = 7)",
            'cnt',
            $params
        ));

        $stale = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom .
            " AND c.status = 2 AND (mc.era_classification IS NULL OR mc.era_classification = '')" .
            " AND c.bill_time < NOW() - INTERVAL " . self::STALE_THRESHOLD_DAYS . " DAY",
            'cnt',
            $params
        ));

        $paid = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom . " AND mc.era_classification LIKE '%paid%'",
            'cnt',
            $params
        ));

        $paidNotPosted = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt " . $baseFrom . " AND mc.era_classification LIKE '%paid%' AND mc.ar_session_id IS NULL",
            'cnt',
            $params
        ));

        return [
            'total' => $total,
            'needingAttention' => $rejected + $denied + $stale + $paidNotPosted,
            'rejected' => $rejected,
            'denied' => $denied,
            'stale' => $stale,
            'paid' => $paid,
            'paidNotPosted' => $paidNotPosted,
        ];
    }

    /**
     * Perform a real-time status check via ClaimRev API.
     *
     * @return array{success: bool, message: string, statusData: array<string, mixed>}
     */
    public static function checkStatus276(int $pid, int $encounter, int $payerType): array
    {
        $pcn = $pid . '-' . $encounter;

        try {
            $api = ClaimRevApi::makeFromGlobals();
            $model = new ClaimSearchModel();
            $model->patientControlNumbers = [$pcn];
            $model->pagingSearch->pageSize = 1;
            $model->pagingSearch->pageIndex = 0;

            $result = $api->searchClaims($model);
            $rawClaims = $result['results'] ?? [];
            $claims = is_array($rawClaims) ? $rawClaims : [];
        } catch (ClaimRevException) {
            return ['success' => false, 'message' => 'Failed to connect to ClaimRev', 'statusData' => []];
        }

        if ($claims === []) {
            self::logEvent(
                $pid,
                $encounter,
                $payerType,
                self::EVENT_STATUS_CHECK_276,
                self::SOURCE_CLAIMREV,
                statusDescription: 'Not found in ClaimRev'
            );

            // Update check date even if not found
            QueryUtils::sqlStatementThrowException(
                "UPDATE mod_claimrev_claims SET last_status_check_date = NOW() WHERE pid = ? AND encounter = ? AND payer_type = ?",
                [$pid, $encounter, $payerType]
            );

            return ['success' => true, 'message' => 'Claim not found in ClaimRev', 'statusData' => []];
        }

        $crClaim = $claims[0];
        if (!is_array($crClaim)) {
            return ['success' => false, 'message' => 'Invalid claim data', 'statusData' => []];
        }
        /** @var array<string, mixed> $crClaim */

        // Get old state for comparison
        $oldRecord = self::getClaimRecord($pid, $encounter, $payerType);

        // Update tracking record
        self::upsertClaimRecord($pid, $encounter, $payerType, $crClaim);

        // Update check date
        QueryUtils::sqlStatementThrowException(
            "UPDATE mod_claimrev_claims SET last_status_check_date = NOW() WHERE pid = ? AND encounter = ? AND payer_type = ?",
            [$pid, $encounter, $payerType]
        );

        // Build change description
        $changes = [];
        $newStatusName = TypeCoerce::asString($crClaim['statusName'] ?? '');
        $oldStatusName = TypeCoerce::asString($oldRecord['claimrev_status_name'] ?? '');
        if ($newStatusName !== $oldStatusName && $newStatusName !== '') {
            $changes[] = "Status: {$oldStatusName} -> {$newStatusName}";
        }

        $newPayerAcc = TypeCoerce::asString($crClaim['payerAcceptanceStatusName'] ?? '');
        $oldPayerAcc = TypeCoerce::asString($oldRecord['payer_acceptance_status_name'] ?? '');
        if ($newPayerAcc !== $oldPayerAcc && $newPayerAcc !== '') {
            $changes[] = "Payer: {$oldPayerAcc} -> {$newPayerAcc}";
        }

        $newEra = TypeCoerce::asString($crClaim['eraClassification'] ?? '');
        $oldEra = TypeCoerce::asString($oldRecord['era_classification'] ?? '');
        if ($newEra !== $oldEra && $newEra !== '') {
            $changes[] = "ERA: {$oldEra} -> {$newEra}";
        }

        $detail = $changes !== [] ? implode('; ', $changes) : 'No changes detected';

        self::logEvent(
            $pid,
            $encounter,
            $payerType,
            self::EVENT_STATUS_CHECK_276,
            self::SOURCE_CLAIMREV,
            statusCode: TypeCoerce::asString($crClaim['statusId'] ?? ''),
            statusDescription: $newStatusName,
            detailText: $detail
        );

        return [
            'success' => true,
            'message' => $changes !== [] ? 'Status updated: ' . implode('; ', $changes) : 'No changes',
            'statusData' => $crClaim,
        ];
    }

    /**
     * Sync a single claim from ClaimRev search results.
     *
     * @param array<string, mixed> $crClaimData Single result from searchClaims
     * @return array{success: bool, message: string}
     */
    public static function syncFromClaimRev(array $crClaimData): array
    {
        $pcn = TypeCoerce::asString($crClaimData['patientControlNumber'] ?? '');
        $parsed = self::parsePcn($pcn);
        if ($parsed === null) {
            return ['success' => false, 'message' => 'Invalid patient control number: ' . $pcn];
        }

        $pid = $parsed['pid'];
        $encounter = $parsed['encounter'];
        $payerType = 1; // Default to primary; could be refined based on claim data

        self::upsertClaimRecord($pid, $encounter, $payerType, $crClaimData);

        self::logEvent(
            $pid,
            $encounter,
            $payerType,
            self::EVENT_CLAIMREV_SYNC,
            self::SOURCE_CLAIMREV,
            statusCode: TypeCoerce::asString($crClaimData['statusId'] ?? ''),
            statusDescription: TypeCoerce::asString($crClaimData['statusName'] ?? ''),
            detailText: 'Synced from ClaimRev'
        );

        return ['success' => true, 'message' => 'Synced'];
    }

    /**
     * Batch sync multiple claims from ClaimRev.
     *
     * @param list<string> $pcns Patient control numbers to sync
     * @return array{synced: int, errors: int, notFound: int, results: list<array{pcn: string, success: bool, message: string}>}
     */
    public static function batchSyncFromClaimRev(array $pcns): array
    {
        $summary = ['synced' => 0, 'errors' => 0, 'notFound' => 0, 'results' => []];

        if ($pcns === []) {
            return $summary;
        }

        try {
            $api = ClaimRevApi::makeFromGlobals();
            $model = new ClaimSearchModel();
            $model->patientControlNumbers = $pcns;
            $model->pagingSearch->pageSize = count($pcns);
            $model->pagingSearch->pageIndex = 0;

            $result = $api->searchClaims($model);
            $rawClaims = $result['results'] ?? [];
            $crClaims = is_array($rawClaims) ? $rawClaims : [];
        } catch (ClaimRevException) {
            $summary['errors'] = count($pcns);
            foreach ($pcns as $pcn) {
                $summary['results'][] = ['pcn' => $pcn, 'success' => false, 'message' => 'ClaimRev connection failed'];
            }
            return $summary;
        }

        // Index by PCN
        /** @var array<string, array<string, mixed>> $crMap */
        $crMap = [];
        foreach ($crClaims as $cr) {
            if (!is_array($cr)) {
                continue;
            }
            $crPcn = TypeCoerce::asString($cr['patientControlNumber'] ?? '');
            if ($crPcn !== '') {
                /** @var array<string, mixed> $cr */
                $crMap[$crPcn] = $cr;
            }
        }

        foreach ($pcns as $pcn) {
            if (isset($crMap[$pcn])) {
                $syncResult = self::syncFromClaimRev($crMap[$pcn]);
                $summary['results'][] = ['pcn' => $pcn, 'success' => $syncResult['success'], 'message' => $syncResult['message']];
                if ($syncResult['success']) {
                    $summary['synced']++;
                } else {
                    $summary['errors']++;
                }
            } else {
                $summary['notFound']++;
                $summary['results'][] = ['pcn' => $pcn, 'success' => false, 'message' => 'Not found in ClaimRev'];
            }
        }

        return $summary;
    }
}
