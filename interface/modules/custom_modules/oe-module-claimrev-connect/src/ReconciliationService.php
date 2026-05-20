<?php

/**
 * Service for reconciling OpenEMR encounters against ClaimRev claim statuses.
 *
 * Queries billed encounters from OpenEMR, looks them up in ClaimRev via
 * the SearchClaimsPaged API (using batch patientControlNumbers), and
 * returns a merged view showing discrepancies.
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
 * @phpstan-type ReconcileRow array{
 *     pid: int,
 *     encounter: int,
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
 *     oeProcessFile: string,
 *     crFound: bool,
 *     crStatusName: string,
 *     crStatusId: int,
 *     crPayerAcceptance: string,
 *     crPayerAcceptanceStatusId: int,
 *     crEraClassification: string,
 *     crPayerPaidAmount: float,
 *     crObjectId: string,
 *     crIsWorked: bool,
 *     discrepancy: string,
 *     discrepancyLevel: string
 * }
 */
class ReconciliationService
{
    /**
     * OpenEMR claim status labels.
     *
     * @var array<int, string>
     */
    private const OE_STATUS_LABELS = [
        0 => 'Not Billed',
        1 => 'Unbilled',
        2 => 'Billed',
        6 => 'Crossover',
        7 => 'Denied',
    ];

    /**
     * Reconcile OpenEMR encounters with ClaimRev.
     *
     * @param array{statusFilter?: string, dateStart?: string, dateEnd?: string, patientFirstName?: string, patientLastName?: string, payerName?: string, discrepancyOnly?: string, pageIndex?: int} $filters
     * @return array{encounters: list<ReconcileRow>, totalRecords: int, claimRevLookupFailed: bool}
     */
    public static function reconcile(array $filters): array
    {
        $pageIndex = (int) ($filters['pageIndex'] ?? 0);
        $pageSize = 50;
        $offset = $pageIndex * $pageSize;
        $statusFilter = $filters['statusFilter'] ?? 'billed';
        $discrepancyOnly = ($filters['discrepancyOnly'] ?? '') !== '';

        // Build WHERE clause for OpenEMR encounters
        $where = [];
        $params = [];

        // Status filter
        if ($statusFilter === 'billed') {
            $where[] = "c.status IN (2, 6)";
        } elseif ($statusFilter === 'denied') {
            $where[] = "c.status = 7";
        } elseif ($statusFilter === 'all_billed') {
            $where[] = "c.status IN (1, 2, 6, 7)";
        }

        // Date filters
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

        // Patient filters
        $patientFirstName = $filters['patientFirstName'] ?? '';
        if ($patientFirstName !== '') {
            $where[] = "p.fname LIKE ?";
            $params[] = '%' . $patientFirstName . '%';
        }
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

        $whereClause = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total matching encounters
        $countSql = "SELECT COUNT(*) AS cnt " .
            "FROM form_encounter e " .
            "JOIN patient_data p ON p.pid = e.pid " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS max_version FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = e.pid AND cv.encounter_id = e.encounter " .
            "JOIN claims c ON c.patient_id = cv.patient_id AND c.encounter_id = cv.encounter_id AND c.version = cv.max_version " .
            "LEFT JOIN insurance_data id ON id.pid = e.pid AND id.type = 'primary' AND id.date <= e.date " .
            "LEFT JOIN insurance_companies ic ON ic.id = id.provider " .
            $whereClause;
        $totalRecords = TypeCoerce::asInt(QueryUtils::fetchSingleValue($countSql, 'cnt', $params));

        // Get encounters with claim status
        $sql = "SELECT e.pid, e.encounter, e.date AS encounter_date, " .
            "p.fname, p.lname, p.DOB, " .
            "c.status AS claim_status, c.bill_process, c.bill_time, c.process_file, " .
            "c.payer_id, c.payer_type, " .
            "ic.name AS payer_name, ic.cms_id AS payer_number, " .
            "COALESCE((SELECT SUM(b.fee) FROM billing b WHERE b.pid = e.pid AND b.encounter = e.encounter AND b.activity = 1), 0) AS total_charges " .
            "FROM form_encounter e " .
            "JOIN patient_data p ON p.pid = e.pid " .
            "JOIN (SELECT patient_id, encounter_id, MAX(version) AS max_version FROM claims GROUP BY patient_id, encounter_id) cv " .
            "  ON cv.patient_id = e.pid AND cv.encounter_id = e.encounter " .
            "JOIN claims c ON c.patient_id = cv.patient_id AND c.encounter_id = cv.encounter_id AND c.version = cv.max_version " .
            "LEFT JOIN insurance_data id ON id.pid = e.pid AND id.type = 'primary' AND id.date <= e.date " .
            "LEFT JOIN insurance_companies ic ON ic.id = id.provider " .
            $whereClause . " " .
            "GROUP BY e.pid, e.encounter " .
            "ORDER BY e.date DESC " .
            "LIMIT {$pageSize} OFFSET {$offset}";
        $rows = QueryUtils::fetchRecords($sql, $params);

        if ($rows === []) {
            return ['encounters' => [], 'totalRecords' => $totalRecords, 'claimRevLookupFailed' => false];
        }

        // Collect base encounter records (without CR fields yet) and PCN map
        /** @var array<string, int> $pcnMap */
        $pcnMap = []; // pcn => row index
        /** @var list<array{pid: int, encounter: int, pcn: string, encounterDate: string, patientName: string, patientDob: string, payerName: string, payerNumber: string, totalCharges: float, billTime: string, oeStatus: int, oeStatusLabel: string, oeProcessFile: string}> $oeRows */
        $oeRows = [];
        foreach ($rows as $idx => $row) {
            $pid = TypeCoerce::asInt($row['pid'] ?? 0);
            $encounter = TypeCoerce::asInt($row['encounter'] ?? 0);
            $pcn = $pid . '-' . $encounter;
            $oeStatus = TypeCoerce::asInt($row['claim_status'] ?? 0);

            $oeRows[] = [
                'pid' => $pid,
                'encounter' => $encounter,
                'pcn' => $pcn,
                'encounterDate' => substr(TypeCoerce::asString($row['encounter_date'] ?? ''), 0, 10),
                'patientName' => TypeCoerce::asString($row['lname'] ?? '') . ', ' . TypeCoerce::asString($row['fname'] ?? ''),
                'patientDob' => substr(TypeCoerce::asString($row['DOB'] ?? ''), 0, 10),
                'payerName' => TypeCoerce::asString($row['payer_name'] ?? ''),
                'payerNumber' => TypeCoerce::asString($row['payer_number'] ?? ''),
                'totalCharges' => TypeCoerce::asFloat($row['total_charges'] ?? 0),
                'billTime' => TypeCoerce::asString($row['bill_time'] ?? ''),
                'oeStatus' => $oeStatus,
                'oeStatusLabel' => self::OE_STATUS_LABELS[$oeStatus] ?? 'Unknown (' . $oeStatus . ')',
                'oeProcessFile' => TypeCoerce::asString($row['process_file'] ?? ''),
            ];

            $pcnMap[$pcn] = $idx;
        }

        // Batch lookup in ClaimRev
        $claimRevLookupFailed = false;
        /** @var array<string, array<string, mixed>> $crByPcn */
        $crByPcn = [];
        try {
            $pcns = array_keys($pcnMap);
            $crResults = self::lookupClaimRev($pcns);

            foreach ($crResults as $crClaim) {
                $crPcn = TypeCoerce::asString($crClaim['patientControlNumber'] ?? '');
                if ($crPcn === '' || !isset($pcnMap[$crPcn])) {
                    continue;
                }
                $crByPcn[$crPcn] = $crClaim;
            }
        } catch (ClaimRevException) {
            $claimRevLookupFailed = true;
        }

        // Build complete ReconcileRow shapes in one pass — assembling the
        // full literal keeps PHPStan's typed-shape inference intact
        // through the computeDiscrepancy() call below.
        $encounters = [];
        foreach ($oeRows as $oeRow) {
            $crClaim = $crByPcn[$oeRow['pcn']] ?? null;

            $row = self::buildReconcileRow($oeRow, $crClaim);

            if ($crClaim !== null) {
                ClaimTrackingService::upsertClaimRecord(
                    $oeRow['pid'],
                    $oeRow['encounter'],
                    1, // primary by default from reconciliation view
                    $crClaim
                );
            }

            $oeHasPayments = self::oeEncounterHasPayments($oeRow['pid'], $oeRow['encounter']);
            $verdict = self::computeDiscrepancy($row, $oeHasPayments);
            $row['discrepancy'] = $verdict['description'];
            $row['discrepancyLevel'] = $verdict['level'];
            $encounters[] = $row;
        }

        // Filter to discrepancies only if requested
        if ($discrepancyOnly) {
            $encounters = array_values(array_filter($encounters, fn(array $enc): bool => $enc['discrepancy'] !== ''));
        }

        return [
            'encounters' => $encounters,
            'totalRecords' => $discrepancyOnly ? count($encounters) : $totalRecords,
            'claimRevLookupFailed' => $claimRevLookupFailed,
        ];
    }

    /**
     * Build a complete ReconcileRow shape from an OE row plus optional
     * ClaimRev claim data. Keeping this as one literal expression lets
     * PHPStan infer the full shape end-to-end (the `+` operator on
     * partial arrays loses the merged shape).
     *
     * @param  array{pid: int, encounter: int, pcn: string, encounterDate: string, patientName: string, patientDob: string, payerName: string, payerNumber: string, totalCharges: float, billTime: string, oeStatus: int, oeStatusLabel: string, oeProcessFile: string} $oeRow
     * @param  array<string, mixed>|null $crClaim
     * @return ReconcileRow
     */
    private static function buildReconcileRow(array $oeRow, ?array $crClaim): array
    {
        return [
            'pid' => $oeRow['pid'],
            'encounter' => $oeRow['encounter'],
            'pcn' => $oeRow['pcn'],
            'encounterDate' => $oeRow['encounterDate'],
            'patientName' => $oeRow['patientName'],
            'patientDob' => $oeRow['patientDob'],
            'payerName' => $oeRow['payerName'],
            'payerNumber' => $oeRow['payerNumber'],
            'totalCharges' => $oeRow['totalCharges'],
            'billTime' => $oeRow['billTime'],
            'oeStatus' => $oeRow['oeStatus'],
            'oeStatusLabel' => $oeRow['oeStatusLabel'],
            'oeProcessFile' => $oeRow['oeProcessFile'],
            'crFound' => $crClaim !== null,
            'crStatusName' => $crClaim !== null ? TypeCoerce::asString($crClaim['statusName'] ?? '') : '',
            'crStatusId' => $crClaim !== null ? TypeCoerce::asInt($crClaim['statusId'] ?? 0) : 0,
            'crPayerAcceptance' => $crClaim !== null ? TypeCoerce::asString($crClaim['payerAcceptanceStatusName'] ?? '') : '',
            'crPayerAcceptanceStatusId' => $crClaim !== null ? TypeCoerce::asInt($crClaim['payerAcceptanceStatusId'] ?? 0) : 0,
            'crEraClassification' => $crClaim !== null ? TypeCoerce::asString($crClaim['eraClassification'] ?? '') : '',
            'crPayerPaidAmount' => $crClaim !== null ? TypeCoerce::asFloat($crClaim['payerPaidAmount'] ?? 0) : 0.0,
            'crObjectId' => $crClaim !== null ? TypeCoerce::asString($crClaim['objectId'] ?? '') : '',
            'crIsWorked' => $crClaim !== null && TypeCoerce::asBool($crClaim['isWorked'] ?? false),
            'discrepancy' => '',
            'discrepancyLevel' => '',
        ];
    }

    /**
     * Batch lookup claims in ClaimRev by patient control numbers.
     *
     * @param list<string> $pcns Patient control numbers (pid-encounter format)
     * @return list<array<string, mixed>> ClaimRev claim results
     */
    private static function lookupClaimRev(array $pcns): array
    {
        if ($pcns === []) {
            return [];
        }

        $api = ClaimRevApi::makeFromGlobals();

        $model = new ClaimSearchModel();
        $model->patientControlNumbers = $pcns;
        $model->pagingSearch->pageSize = count($pcns);
        $model->pagingSearch->pageIndex = 0;

        $result = $api->searchClaims($model);
        $results = $result['results'] ?? [];
        if (!is_array($results)) {
            return [];
        }

        $out = [];
        foreach ($results as $r) {
            if (is_array($r)) {
                /** @var array<string, mixed> $rTyped */
                $rTyped = $r;
                $out[] = $rTyped;
            }
        }
        return $out;
    }

    /**
     * Pure discrepancy classifier — given a merged encounter row plus the
     * pre-computed "OE has payments?" signal, return the description + level.
     * No DB, no API, no globals.
     *
     * Empty description means "no discrepancy" (level will be empty too).
     *
     * @param ReconcileRow $enc
     * @return array{description: string, level: string}
     */
    public static function computeDiscrepancy(array $enc, bool $oeHasPayments): array
    {
        $oeStatus = $enc['oeStatus'];
        $crFound = $enc['crFound'];
        $crStatusId = $enc['crStatusId'];
        $crPayerAcceptanceStatusId = $enc['crPayerAcceptanceStatusId'];
        $crEra = $enc['crEraClassification'];

        // Billed in OE but not found in ClaimRev
        if ($oeStatus === 2 && !$crFound) {
            return ['description' => 'Billed in OpenEMR but not found in ClaimRev', 'level' => 'danger'];
        }

        if (!$crFound) {
            return ['description' => '', 'level' => ''];
        }

        // ClaimRev says rejected but OE still shows billed
        $crRejected = in_array($crStatusId, [10, 16, 17], true) || $crPayerAcceptanceStatusId === 3;
        if ($crRejected && $oeStatus === 2) {
            return ['description' => 'Rejected in ClaimRev but still Billed in OpenEMR', 'level' => 'danger'];
        }

        // OE says denied but ClaimRev says accepted
        if ($oeStatus === 7 && $crPayerAcceptanceStatusId === 4) {
            return ['description' => 'Denied in OpenEMR but Accepted in ClaimRev', 'level' => 'warning'];
        }

        // Has ERA/payment but not posted to OE
        if ($crEra !== '' && stripos($crEra, 'paid') !== false && !$oeHasPayments) {
            return ['description' => 'ERA shows paid but no payment posted in OpenEMR', 'level' => 'warning'];
        }

        // ERA denied but OE not marked denied
        if ($crEra !== '' && stripos($crEra, 'denied') !== false && $oeStatus !== 7) {
            return ['description' => 'ERA shows denied but OpenEMR not marked as denied', 'level' => 'warning'];
        }

        return ['description' => '', 'level' => ''];
    }

    /**
     * Returns true if the OE encounter has at least one positive ar_activity
     * payment row. Split out from computeDiscrepancy() so the discrepancy
     * classifier itself can stay pure and unit-testable.
     */
    private static function oeEncounterHasPayments(int $pid, int $encounter): bool
    {
        $count = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt FROM ar_activity WHERE pid = ? AND encounter = ? AND deleted IS NULL AND pay_amount > 0",
            'cnt',
            [$pid, $encounter]
        ));
        return $count > 0;
    }
}
