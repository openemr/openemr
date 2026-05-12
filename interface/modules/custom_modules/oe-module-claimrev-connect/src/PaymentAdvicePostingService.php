<?php

/**
 * Service for posting ClaimRev payment advice data to OpenEMR claims.
 *
 * Translates structured ERA payment data from the ClaimRev API into
 * OpenEMR ar_session / ar_activity records using the existing SLEOB
 * posting functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;

class PaymentAdvicePostingService
{
    /**
     * Reference prefix used in ar_session to identify ClaimRev postings.
     */
    public const REFERENCE_PREFIX = 'ClaimRev-';

    /**
     * Map of X12 835 claim status codes (CLP02) to display labels.
     *
     * @var array<int, string>
     */
    public const CLAIM_STATUS_LABELS = [
        1 => 'Processed as Primary',
        2 => 'Processed as Secondary',
        3 => 'Processed as Tertiary',
        4 => 'Denied',
        5 => 'Pended',
        22 => 'Reversal of Previous Payment',
    ];

    /**
     * Build the ar_session.reference value used for idempotency lookup of a
     * payment advice. The format is fixed: anything posting under this prefix
     * is considered a ClaimRev-originated payment session, and re-posting the
     * same paymentAdviceId must match the same reference.
     */
    public static function buildIdempotencyReference(string $paymentAdviceId): string
    {
        return self::REFERENCE_PREFIX . $paymentAdviceId;
    }

    /**
     * Parse a ClaimRev patient control number into pid + encounter.
     *
     * The PCN is emitted by the ClaimRev integration as "{pid}-{encounter}"
     * (or "{pid} {encounter}"); both pid and encounter must be positive
     * integers. Returns null on any unparsable input so the caller can
     * surface a single error path rather than separate "couldn't parse"
     * and "got 0/0" cases.
     *
     * @return array{pid: int, encounter: int}|null
     */
    public static function parsePatientControlNumber(string $pcn): ?array
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
     * Look up the human-readable label for an X12 835 claim status code.
     *
     * Codes outside the 835 vocabulary fall through to the raw code so the
     * UI surfaces something instead of going blank.
     */
    public static function getClaimStatusLabel(string $code): string
    {
        return is_numeric($code)
            ? (self::CLAIM_STATUS_LABELS[(int) $code] ?? $code)
            : $code;
    }

    /**
     * Sum charged / paid / adjusted amounts across an 835 service lines
     * payload. Pure: no DB, no network, no globals.
     *
     * Each entry is expected to look like:
     *   ['chargeAmount' => float, 'paymentAmount' => float,
     *    'adjustmentGroups' => [['adjustments' => [['adjustmentAmount' => float]]]]]
     *
     * @param list<array<string, mixed>> $servicePaymentInfos
     * @return array{billed: float, paid: float, adjusted: float}
     */
    public static function sumServiceAmounts(array $servicePaymentInfos): array
    {
        $billed = 0.0;
        $paid = 0.0;
        $adjusted = 0.0;
        foreach ($servicePaymentInfos as $svc) {
            $billed += TypeCoerce::asFloat($svc['chargeAmount'] ?? 0);
            $paid += TypeCoerce::asFloat($svc['paymentAmount'] ?? 0);
            $svcAdjGroups = $svc['adjustmentGroups'] ?? [];
            if (!is_array($svcAdjGroups)) {
                continue;
            }
            foreach ($svcAdjGroups as $group) {
                if (!is_array($group)) {
                    continue;
                }
                $adjustments = $group['adjustments'] ?? [];
                if (!is_array($adjustments)) {
                    continue;
                }
                foreach ($adjustments as $adj) {
                    if (is_array($adj)) {
                        $adjusted += TypeCoerce::asFloat($adj['adjustmentAmount'] ?? 0);
                    }
                }
            }
        }
        return ['billed' => $billed, 'paid' => $paid, 'adjusted' => $adjusted];
    }

    /**
     * Check if a payment advice has already been posted to OpenEMR.
     *
     * Uses two checks:
     * 1. ar_session.reference matching our ClaimRev reference format
     * 2. ar_activity.payer_claim_number for the specific encounter
     *
     * @return array{posted: bool, session_id: int|null, details: string}
     */
    public static function isAlreadyPosted(string $paymentAdviceId, int $pid = 0, int $encounter = 0): array
    {
        $reference = self::buildIdempotencyReference($paymentAdviceId);

        // Check 1: session-level duplicate
        // The reference may be stored with a prefix like "ePay - " by arPostSession
        $row = QueryUtils::fetchSingleValue(
            "SELECT session_id FROM ar_session WHERE reference LIKE ?",
            'session_id',
            ['%' . $reference]
        );

        if ($row !== null) {
            $sessionIdInt = TypeCoerce::asInt($row);
            return [
                'posted' => true,
                'session_id' => $sessionIdInt,
                'details' => 'Payment session already exists (session_id: ' . $sessionIdInt . ')',
            ];
        }

        // Check 2: activity-level duplicate via session join (if we have encounter info)
        if ($pid > 0 && $encounter > 0) {
            $count = QueryUtils::fetchSingleValue(
                "SELECT COUNT(*) AS cnt FROM ar_activity a " .
                "JOIN ar_session s ON s.session_id = a.session_id " .
                "WHERE a.pid = ? AND a.encounter = ? AND s.reference LIKE ? AND a.deleted IS NULL",
                'cnt',
                [$pid, $encounter, '%' . $reference]
            );

            if (TypeCoerce::asInt($count) > 0) {
                return [
                    'posted' => true,
                    'session_id' => null,
                    'details' => 'Payment activity records already exist for this encounter',
                ];
            }
        }

        return ['posted' => false, 'session_id' => null, 'details' => ''];
    }

    /**
     * Retrieve posting details for an already-posted payment advice.
     *
     * Returns session info and the individual line items (payments and adjustments)
     * from ar_session / ar_activity so the UI can display what was posted.
     *
     * @return array{
     *   found: bool,
     *   session_id: int|null,
     *   check_date: string,
     *   pay_total: float,
     *   post_user: string,
     *   created_time: string,
     *   lines: list<array{code: string, modifier: string, pay_amount: float, adj_amount: float, memo: string, reason_code: string, account_code: string, post_date: string}>
     * }
     */
    public static function getPostingDetails(string $paymentAdviceId, int $pid = 0, int $encounter = 0): array
    {
        $reference = self::buildIdempotencyReference($paymentAdviceId);
        $result = [
            'found' => false,
            'session_id' => null,
            'check_date' => '',
            'pay_total' => 0.0,
            'post_user' => '',
            'created_time' => '',
            'lines' => [],
        ];

        // Find the session
        // The reference may be stored with a prefix like "ePay - " by arPostSession
        $session = QueryUtils::querySingleRow(
            "SELECT s.session_id, s.check_date, s.pay_total, s.created_time, u.username " .
            "FROM ar_session s " .
            "LEFT JOIN users u ON u.id = s.user_id " .
            "WHERE s.reference LIKE ?",
            ['%' . $reference]
        );

        if ($session === [] || $session === false) {
            return $result;
        }

        $result['found'] = true;
        $result['session_id'] = TypeCoerce::asInt($session['session_id'] ?? 0);
        $result['check_date'] = TypeCoerce::asString($session['check_date'] ?? '');
        $result['pay_total'] = TypeCoerce::asFloat($session['pay_total'] ?? 0);
        $result['post_user'] = TypeCoerce::asString($session['username'] ?? '');
        $result['created_time'] = TypeCoerce::asString($session['created_time'] ?? '');

        // Get activity lines for this session + encounter
        $whereParams = [$result['session_id']];
        $encounterClause = '';
        if ($pid > 0 && $encounter > 0) {
            $encounterClause = ' AND a.pid = ? AND a.encounter = ?';
            $whereParams[] = $pid;
            $whereParams[] = $encounter;
        }

        $activities = QueryUtils::fetchRecords(
            "SELECT a.code, a.modifier, a.pay_amount, a.adj_amount, a.memo, " .
            "a.reason_code, a.post_date, a.account_code " .
            "FROM ar_activity a " .
            "WHERE a.session_id = ? AND a.deleted IS NULL" . $encounterClause .
            " ORDER BY a.sequence_no",
            $whereParams
        );

        foreach ($activities as $act) {
            $result['lines'][] = [
                'code' => TypeCoerce::asString($act['code'] ?? ''),
                'modifier' => TypeCoerce::asString($act['modifier'] ?? ''),
                'pay_amount' => TypeCoerce::asFloat($act['pay_amount'] ?? 0),
                'adj_amount' => TypeCoerce::asFloat($act['adj_amount'] ?? 0),
                'memo' => TypeCoerce::asString($act['memo'] ?? ''),
                'reason_code' => TypeCoerce::asString($act['reason_code'] ?? ''),
                'account_code' => TypeCoerce::asString($act['account_code'] ?? ''),
                'post_date' => TypeCoerce::asString($act['post_date'] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * Validate and preview what would be posted without actually posting.
     *
     * @param array<string, mixed> $paymentData Single result from SearchPaymentInfo
     * @return array{
     *   canPost: bool,
     *   alreadyPosted: bool,
     *   requiresApproval: bool,
     *   approvalReason: string,
     *   pid: int,
     *   encounter: int,
     *   errors: list<string>,
     *   warnings: list<string>,
     *   serviceLines: list<array{code: string, modifier: string, codekey: string, charged: float, paid: float, adjustments: list<array{groupCode: string, reasonCode: string, amount: float}>, totalAdjusted: float, matched: bool}>,
     *   checkNumber: string,
     *   checkDate: string,
     *   payerName: string,
     *   payerNumber: string,
     *   totalPaid: float,
     *   totalAdjusted: float,
     *   totalBilled: float,
     *   claimStatusCode: string,
     *   claimStatusLabel: string
     * }
     */
    public static function preview(array $paymentData): array
    {
        $result = [
            'canPost' => false,
            'alreadyPosted' => false,
            'requiresApproval' => false,
            'approvalReason' => '',
            'pid' => 0,
            'encounter' => 0,
            'errors' => [],
            'warnings' => [],
            'serviceLines' => [],
            'checkNumber' => '',
            'checkDate' => '',
            'payerName' => TypeCoerce::asString($paymentData['payerName'] ?? ''),
            'payerNumber' => TypeCoerce::asString($paymentData['payerNumber'] ?? ''),
            'totalPaid' => 0.0,
            'totalAdjusted' => 0.0,
            'totalBilled' => 0.0,
            'claimStatusCode' => '',
            'claimStatusLabel' => '',
        ];

        $paymentAdviceId = TypeCoerce::asString($paymentData['paymentAdviceId'] ?? '');
        $paymentInfo = is_array($paymentData['paymentInfo'] ?? null) ? $paymentData['paymentInfo'] : [];
        $checkInfo = is_array($paymentData['checkInformation'] ?? null) ? $paymentData['checkInformation'] : [];

        $result['checkNumber'] = TypeCoerce::asString($checkInfo['checkNumber'] ?? '');
        $result['checkDate'] = substr(TypeCoerce::asString($checkInfo['checkDate'] ?? ''), 0, 10);
        $result['totalPaid'] = TypeCoerce::asFloat($checkInfo['totalActualProviderPaymentAmt'] ?? 0);

        // Parse patient control number
        $pcn = TypeCoerce::asString($paymentInfo['patientControlNumber'] ?? '');
        $parsed = self::parsePatientControlNumber($pcn);
        if ($parsed === null) {
            $result['errors'][] = 'Cannot parse patient control number: ' . $pcn;
            return $result;
        }
        $pid = $parsed['pid'];
        $encounter = $parsed['encounter'];
        $result['pid'] = $pid;
        $result['encounter'] = $encounter;

        // Check for duplicate
        $dupeCheck = self::isAlreadyPosted($paymentAdviceId, $pid, $encounter);
        if ($dupeCheck['posted']) {
            $result['alreadyPosted'] = true;
            $result['errors'][] = $dupeCheck['details'];
            return $result;
        }

        // Verify encounter exists
        $ferow = QueryUtils::querySingleRow(
            "SELECT e.pid, e.encounter, e.date, e.last_level_closed, p.fname, p.lname FROM form_encounter AS e " .
            "JOIN patient_data AS p ON p.pid = e.pid " .
            "WHERE e.pid = ? AND e.encounter = ?",
            [$pid, $encounter]
        );

        if ($ferow === [] || $ferow === false) {
            $result['errors'][] = 'Encounter not found in OpenEMR: pid=' . $pid . ' encounter=' . $encounter;
            return $result;
        }

        // Claim status
        $csc = TypeCoerce::asString($paymentInfo['claimStatusCode'] ?? '');

        // Check for secondary/tertiary posted before primary
        $lastLevelClosed = TypeCoerce::asInt($ferow['last_level_closed'] ?? 0);
        if (in_array($csc, ['2', '20'], true) && $lastLevelClosed < 1) {
            $result['warnings'][] = 'Primary insurance has not been posted yet. Posting secondary first may result in incorrect adjustments.';
            $result['requiresApproval'] = true;
            $result['approvalReason'] = 'secondary_before_primary';
        } elseif (in_array($csc, ['3', '21'], true) && $lastLevelClosed < 2) {
            $result['warnings'][] = 'Secondary insurance has not been posted yet. Posting tertiary first may result in incorrect adjustments.';
            $result['requiresApproval'] = true;
            $result['approvalReason'] = 'tertiary_before_secondary';
        }
        $result['claimStatusCode'] = $csc;
        $result['claimStatusLabel'] = self::getClaimStatusLabel($csc);

        if ($csc === '4') {
            $result['warnings'][] = 'Claim was denied — reason codes will be stored but no payments posted';
        } elseif ($csc === '22') {
            $result['warnings'][] = 'Payment reversal — negative adjustment will be applied. Requires manual approval.';
            $result['requiresApproval'] = true;
            $result['approvalReason'] = 'reversal';
        } elseif ($csc === '5') {
            $result['warnings'][] = 'Claim is pended by the payer — posting now may need correction later.';
            $result['requiresApproval'] = true;
            $result['approvalReason'] = 'pended';
        }

        // Get existing billing codes for matching
        $existingCodesRaw = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);
        /** @var array<string, mixed> $existingCodes */
        $existingCodes = is_array($existingCodesRaw) ? $existingCodesRaw : [];

        // Match service lines
        // Field names from ClaimRev .NET model: ServicePaymentInfo
        //   procedureCode, modifier1..4, chargeAmount, paymentAmount
        //   adjustmentGroups[].groupCode, adjustments[].reasonCode, adjustmentAmount
        $servicePaymentInfos = is_array($paymentInfo['servicePaymentInfos'] ?? null) ? $paymentInfo['servicePaymentInfos'] : [];
        foreach ($servicePaymentInfos as $svc) {
            if (!is_array($svc)) {
                continue;
            }
            $code = TypeCoerce::asString($svc['procedureCode'] ?? '');
            $modifier = TypeCoerce::asString($svc['modifier1'] ?? '');
            $codekey = $code;
            if ($modifier !== '') {
                $codekey .= ':' . $modifier;
            }
            // Append additional modifiers if present
            foreach (['modifier2', 'modifier3', 'modifier4'] as $modKey) {
                $modVal = TypeCoerce::asString($svc[$modKey] ?? '');
                if ($modVal !== '') {
                    $codekey .= ':' . $modVal;
                }
            }

            $charged = TypeCoerce::asFloat($svc['chargeAmount'] ?? 0);
            $paid = TypeCoerce::asFloat($svc['paymentAmount'] ?? 0);
            $totalAdj = 0.0;

            $adjustments = [];
            $svcAdjGroups = is_array($svc['adjustmentGroups'] ?? null) ? $svc['adjustmentGroups'] : [];
            foreach ($svcAdjGroups as $group) {
                if (!is_array($group)) {
                    continue;
                }
                $groupCode = TypeCoerce::asString($group['groupCode'] ?? '');
                $groupAdjustments = is_array($group['adjustments'] ?? null) ? $group['adjustments'] : [];
                foreach ($groupAdjustments as $adj) {
                    if (!is_array($adj)) {
                        continue;
                    }
                    $adjAmount = TypeCoerce::asFloat($adj['adjustmentAmount'] ?? 0);
                    $totalAdj += $adjAmount;
                    $adjustments[] = [
                        'groupCode' => $groupCode,
                        'reasonCode' => TypeCoerce::asString($adj['reasonCode'] ?? ''),
                        'amount' => $adjAmount,
                    ];
                }
            }

            $matched = isset($existingCodes[$codekey]);
            if (!$matched && $code !== '' && !$modifier) {
                // Try matching without modifier
                foreach (array_keys($existingCodes) as $existingKey) {
                    if (str_starts_with($existingKey, $code . ':') || $existingKey === $code) {
                        $matched = true;
                        $codekey = $existingKey;
                        break;
                    }
                }
            }

            if (!$matched) {
                $result['warnings'][] = 'Service line ' . $codekey . ' not found in OpenEMR billing';
            }

            $result['serviceLines'][] = [
                'code' => $code,
                'modifier' => $modifier,
                'codekey' => $codekey,
                'charged' => $charged,
                'paid' => $paid,
                'adjustments' => $adjustments,
                'totalAdjusted' => $totalAdj,
                'matched' => $matched,
            ];

            $result['totalBilled'] += $charged;
            $result['totalAdjusted'] += $totalAdj;
        }

        // No code path between the early-return errors above and here adds
        // to \$result['errors'], but keep this assignment as an explicit
        // contract guard rather than hard-coding canPost = true.
        $result['canPost'] = true;
        return $result;
    }

    /**
     * Post a single payment advice to OpenEMR.
     *
     * @param array<string, mixed> $paymentData Single result from SearchPaymentInfo
     * @param bool $skipMarkWorked If true, skip marking as worked on ClaimRev (e.g. test mode)
     * @return array{success: bool, session_id: int|null, message: string, posted_lines: int, requiresApproval?: bool, approvalReason?: string}
     */
    public static function post(array $paymentData, bool $skipMarkWorked = false, bool $approved = false): array
    {
        // Serialize concurrent posts of the same payment advice across
        // PHP workers. Without this, two near-simultaneous submits can both
        // pass isAlreadyPosted() before either inserts the ar_session row —
        // ar_session.reference is not unique-keyed, so both inserts succeed
        // and the same payment posts twice. A MySQL named lock keyed by
        // paymentAdviceId gives a single posting window per advice without
        // touching core schema.
        $paymentAdviceIdForLock = TypeCoerce::asString($paymentData['paymentAdviceId'] ?? '');
        $lockName = $paymentAdviceIdForLock !== ''
            ? 'claimrev_post_' . $paymentAdviceIdForLock
            : '';
        $heldLock = false;
        if ($lockName !== '') {
            $gotLock = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
                'SELECT GET_LOCK(?, 5) AS l',
                'l',
                [$lockName]
            ));
            if ($gotLock !== 1) {
                return [
                    'success' => false,
                    'session_id' => null,
                    'message' => 'Concurrent post in progress for this advice — please retry',
                    'posted_lines' => 0,
                ];
            }
            $heldLock = true;
        }

        try {
            return self::postWithinLock($paymentData, $skipMarkWorked, $approved);
        } finally {
            if ($heldLock) {
                QueryUtils::sqlStatementThrowException(
                    'DO RELEASE_LOCK(?)',
                    [$lockName]
                );
            }
        }
    }

    /**
     * Inner body of post(). Assumes the per-paymentAdviceId named lock is
     * held by the caller.
     *
     * @param array<string, mixed> $paymentData
     * @return array{success: bool, session_id: int|null, message: string, posted_lines: int, requiresApproval?: bool, approvalReason?: string}
     */
    private static function postWithinLock(array $paymentData, bool $skipMarkWorked, bool $approved): array
    {
        $preview = self::preview($paymentData);

        if ($preview['alreadyPosted']) {
            return [
                'success' => false,
                'session_id' => null,
                'message' => 'Already posted: ' . ($preview['errors'][0] ?? 'duplicate detected'),
                'posted_lines' => 0,
            ];
        }

        if (!$approved && $preview['requiresApproval']) {
            return [
                'success' => false,
                'session_id' => null,
                'message' => 'Requires approval: ' . implode('; ', $preview['warnings']),
                'posted_lines' => 0,
                'requiresApproval' => true,
                'approvalReason' => $preview['approvalReason'],
            ];
        }

        if (!$preview['canPost']) {
            return [
                'success' => false,
                'session_id' => null,
                'message' => 'Cannot post: ' . implode('; ', $preview['errors']),
                'posted_lines' => 0,
            ];
        }

        $paymentAdviceId = TypeCoerce::asString($paymentData['paymentAdviceId'] ?? '');
        $paymentInfo = is_array($paymentData['paymentInfo'] ?? null) ? $paymentData['paymentInfo'] : [];
        $checkInfo = is_array($paymentData['checkInformation'] ?? null) ? $paymentData['checkInformation'] : [];
        $pid = $preview['pid'];
        $encounter = $preview['encounter'];
        $csc = $preview['claimStatusCode'];

        $checkNumber = TypeCoerce::asString($checkInfo['checkNumber'] ?? '');
        $checkDate = $preview['checkDate'] !== '' ? $preview['checkDate'] : date('Y-m-d');
        $payTotal = TypeCoerce::asFloat($checkInfo['totalActualProviderPaymentAmt'] ?? 0);
        $reference = self::buildIdempotencyReference($paymentAdviceId);
        $memo = 'Chk#' . ($checkNumber !== '' ? $checkNumber : 'N/A') . ' ' . $pid . '-' . $encounter;

        // Determine payer type from claim status
        $payerType = match ($csc) {
            '2', '20' => 2,
            '3', '21' => 3,
            default => 1,
        };
        $inslabel = 'Ins' . $payerType;

        // Look up the insurance company ID
        $serviceDateRaw = TypeCoerce::asString($paymentInfo['serviceDateStart'] ?? '');
        $serviceDate = $serviceDateRaw !== '' ? substr($serviceDateRaw, 0, 10) : date('Y-m-d');
        $insuranceId = SLEOB::arGetPayerID($pid, $serviceDate, $payerType);

        // Handle denial case
        if ($csc === '4') {
            $codeValue = '';
            foreach ($preview['serviceLines'] as $svc) {
                foreach ($svc['adjustments'] as $adj) {
                    $codeValue .= $svc['code'] . '_' . $svc['modifier'] . '_' . $adj['groupCode'] . '_' . $adj['reasonCode'] . ',';
                }
            }
            $codeValue = rtrim($codeValue, ',');

            BillingUtilities::updateClaim(true, $pid, $encounter, $insuranceId, $payerType, 7, 0, $codeValue);

            ClaimTrackingService::logEvent(
                $pid,
                $encounter,
                $payerType,
                ClaimTrackingService::EVENT_DENIED,
                ClaimTrackingService::SOURCE_ERA,
                statusCode: '4',
                statusDescription: 'Denial recorded from ERA',
                detailText: $codeValue,
            );

            // Mark as worked on the ClaimRev side
            if (!$skipMarkWorked) {
                self::markWorkedOnClaimRev($paymentData);
            }

            return [
                'success' => true,
                'session_id' => null,
                'message' => 'Denial recorded with reason codes',
                'posted_lines' => 0,
            ];
        }

        // Create payment session
        $sessionId = TypeCoerce::asInt(SLEOB::arPostSession(
            payer_id: $insuranceId,
            check_number: $reference,
            check_date: $checkDate,
            pay_total: $payTotal,
            post_to_date: date('Y-m-d'),
            deposit_date: date('Y-m-d'),
            debug: false,
        ));

        if ($sessionId === 0) {
            return [
                'success' => false,
                'session_id' => null,
                'message' => 'Failed to create payment session',
                'posted_lines' => 0,
            ];
        }

        $postedLines = 0;
        $primary = ($payerType === 1);

        foreach ($preview['serviceLines'] as $svc) {
            $codekey = $svc['codekey'];
            $codetype = '';
            if ($svc['matched']) {
                // Try to determine code type from billing
                $billingRow = QueryUtils::querySingleRow(
                    "SELECT code_type FROM billing WHERE pid = ? AND encounter = ? AND code = ? AND activity = 1 LIMIT 1",
                    [$pid, $encounter, $svc['code']]
                );
                $codetype = TypeCoerce::asString($billingRow['code_type'] ?? '');
            }

            $payerControlNumber = TypeCoerce::asString($paymentInfo['payerControlNumber'] ?? '');

            // Post payment for this service line
            if ($svc['paid'] != 0.0) {
                // @phpstan-ignore staticMethod.deprecated
                SLEOB::arPostPayment(
                    patient_id: (string) $pid,
                    encounter_id: (string) $encounter,
                    session_id: (string) $sessionId,
                    amount: (string) $svc['paid'],
                    code: $svc['codekey'],
                    payer_type: (string) $payerType,
                    memo: $memo,
                    codetype: $codetype,
                    date: $checkDate,
                    payer_claim_number: $payerControlNumber !== '' ? $payerControlNumber : null,
                );
                $postedLines++;
            }

            // Post adjustments
            foreach ($svc['adjustments'] as $adj) {
                if ($adj['groupCode'] === 'PR' || !$primary) {
                    // Patient responsibility or non-primary: post as zero-dollar memo
                    $reason = $primary
                        ? match ($adj['reasonCode']) {
                            '1' => "$inslabel dedbl: ",
                            '2' => "$inslabel coins: ",
                            '3' => "$inslabel copay: ",
                            default => "$inslabel ptresp: ",
                        }
                        : "$inslabel note " . $adj['reasonCode'] . ': ';

                    $reason .= sprintf("%.2f", $adj['amount']);

                    // @phpstan-ignore staticMethod.deprecated
                    SLEOB::arPostAdjustment(
                        patient_id: (string) $pid,
                        encounter_id: (string) $encounter,
                        session_id: (string) $sessionId,
                        amount: '0',
                        code: $svc['codekey'],
                        payer_type: (string) $payerType,
                        reason: $reason,
                        codetype: $codetype,
                    );
                } elseif ($adj['amount'] != 0.0) {
                    // @phpstan-ignore staticMethod.deprecated
                    SLEOB::arPostAdjustment(
                        patient_id: (string) $pid,
                        encounter_id: (string) $encounter,
                        session_id: (string) $sessionId,
                        amount: (string) $adj['amount'],
                        code: $svc['codekey'],
                        payer_type: (string) $payerType,
                        reason: "Adjust code " . $adj['reasonCode'],
                        codetype: $codetype,
                    );
                }
            }
        }

        // Update claim level and check for secondary
        $levelDone = $payerType;
        QueryUtils::sqlStatementThrowException(
            "UPDATE form_encounter SET last_level_closed = ? WHERE pid = ? AND encounter = ?",
            [$levelDone, $pid, $encounter]
        );

        // Mark claim as Processed (status 3) in OpenEMR
        BillingUtilities::updateClaim(false, $pid, $encounter, -1, -1, 3);

        // Log to claim tracking
        ClaimTrackingService::logEvent(
            $pid,
            $encounter,
            $payerType,
            ClaimTrackingService::EVENT_PAYMENT_POSTED,
            ClaimTrackingService::SOURCE_ERA,
            statusCode: $csc,
            statusDescription: 'Payment posted from ERA',
            detailText: "Session $sessionId, $postedLines lines, check #" . ($checkNumber ?: 'N/A'),
            amount: $payTotal,
        );

        // \$sessionId is guaranteed > 0 here (we returned earlier if it was 0)
        ClaimTrackingService::linkPaymentSession($pid, $encounter, $payerType, $sessionId);

        // If primary and secondary insurance exists, re-queue for secondary billing
        if ($primary && SLEOB::arGetPayerID($pid, $serviceDate, 2)) {
            SLEOB::arSetupSecondary($pid, $encounter, false, 0);
        }

        // Mark as worked on the ClaimRev side
        if (!$skipMarkWorked) {
            self::markWorkedOnClaimRev($paymentData);
        }

        return [
            'success' => true,
            'session_id' => $sessionId,
            'message' => "Posted $postedLines service line(s), session $sessionId",
            'posted_lines' => $postedLines,
        ];
    }

    /**
     * Batch post multiple payment advice records.
     *
     * Only posts claims with status 1, 2, or 3 (processed). Skips denials,
     * reversals, pended, and already-posted items.
     *
     * @param list<array<string, mixed>> $paymentDataList Results from SearchPaymentInfo
     * @return array{
     *   totalProcessed: int,
     *   totalPosted: int,
     *   totalSkipped: int,
     *   totalErrors: int,
     *   results: list<array{paymentAdviceId: string, success: bool, message: string, skipped: bool}>
     * }
     */
    public static function batchPost(array $paymentDataList, bool $skipMarkWorked = false): array
    {
        $summary = [
            'totalProcessed' => 0,
            'totalPosted' => 0,
            'totalSkipped' => 0,
            'totalErrors' => 0,
            'totalDeferred' => 0,
            'results' => [],
            'deferred' => [],
        ];

        foreach ($paymentDataList as $paymentData) {
            $summary['totalProcessed']++;
            $paymentAdviceId = TypeCoerce::asString($paymentData['paymentAdviceId'] ?? '');
            $paymentInfo = is_array($paymentData['paymentInfo'] ?? null) ? $paymentData['paymentInfo'] : [];
            $csc = TypeCoerce::asString($paymentInfo['claimStatusCode'] ?? '');

            // Reversals and pended claims need individual approval — defer them
            if (in_array($csc, ['5', '22'], true)) {
                $label = $csc === '22' ? 'Reversal' : 'Pended';
                $summary['totalDeferred']++;
                $summary['deferred'][] = [
                    'paymentAdviceId' => $paymentAdviceId,
                    'reason' => $label,
                    'claimStatusCode' => $csc,
                    'patientName' => TypeCoerce::asString($paymentInfo['patientLastName'] ?? '') . ', ' . TypeCoerce::asString($paymentInfo['patientFirstName'] ?? ''),
                    'pcn' => TypeCoerce::asString($paymentInfo['patientControlNumber'] ?? ''),
                    'amount' => TypeCoerce::asFloat($paymentInfo['claimPaymentAmount'] ?? 0),
                ];
                continue;
            }

            // Only auto-post processed claims (1=primary, 2=secondary, 3=tertiary)
            // Denials (4) are also auto-postable since they just record reason codes
            if (!in_array($csc, ['1', '2', '3', '4'], true)) {
                $summary['totalSkipped']++;
                $summary['results'][] = [
                    'paymentAdviceId' => $paymentAdviceId,
                    'success' => false,
                    'message' => 'Skipped: claim status ' . $csc . ' is not a processed claim',
                    'skipped' => true,
                ];
                continue;
            }

            $postResult = self::post($paymentData, $skipMarkWorked);

            if ($postResult['success']) {
                $summary['totalPosted']++;
            } else {
                if (str_starts_with($postResult['message'], 'Already posted')) {
                    $summary['totalSkipped']++;
                } else {
                    $summary['totalErrors']++;
                }
            }

            $summary['results'][] = [
                'paymentAdviceId' => $paymentAdviceId,
                'success' => $postResult['success'],
                'message' => $postResult['message'],
                'skipped' => str_starts_with($postResult['message'], 'Already posted') || str_starts_with($postResult['message'], 'Skipped'),
            ];
        }

        return $summary;
    }

    /**
     * Mark a payment advice as worked on the ClaimRev side.
     *
     * This is a best-effort call — if it fails (e.g. network issue),
     * we don't fail the entire post since the OpenEMR side already succeeded.
     * The API toggles isWorked, so we only call this if it's currently not worked.
     *
     * @param array<string, mixed> $paymentData The full ClaimPaymentAggregation
     */
    private static function markWorkedOnClaimRev(array $paymentData): void
    {
        $paymentInfo = is_array($paymentData['paymentInfo'] ?? null) ? $paymentData['paymentInfo'] : [];
        if (TypeCoerce::asBool($paymentInfo['isWorked'] ?? false)) {
            // Already marked as worked, don't toggle it back
            return;
        }

        try {
            $api = ClaimRevApi::makeFromGlobals();
            $api->markPaymentAdviceWorked($paymentData);
        } catch (ClaimRevException) {
            // Best-effort: OpenEMR posting already succeeded, don't fail over this
        }
    }
}
