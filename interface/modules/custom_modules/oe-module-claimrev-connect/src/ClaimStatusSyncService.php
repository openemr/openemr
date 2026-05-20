<?php

/**
 * Service for syncing ClaimRev claim statuses to OpenEMR and requeuing claims.
 *
 * Maps ClaimRev claim/payer statuses to OpenEMR claim status values and
 * provides requeue-for-billing functionality for rejected/denied claims.
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
use OpenEMR\Common\Database\QueryUtils;

class ClaimStatusSyncService
{
    /**
     * Sync a ClaimRev claim status to the corresponding OpenEMR claim.
     *
     * Maps ClaimRev statusId / payerAcceptanceStatusId to OpenEMR claim status:
     *   - Rejected (statusId=10,16,17 or payerAcceptance=3): OE status=7 (Denied)
     *   - Accepted (payerAcceptance=4): no change needed (already billed)
     *
     * @param array{
     *   patientControlNumber: string,
     *   statusId: int,
     *   statusName: string,
     *   payerAcceptanceStatusId: int,
     *   payerAcceptanceStatusName: string,
     *   errorMessage?: string
     * } $claimData
     * @return array{success: bool, message: string, action: string}
     */
    public static function syncStatus(array $claimData): array
    {
        $pcn = $claimData['patientControlNumber'];
        $parsed = self::parsePcn($pcn);
        if ($parsed === null) {
            return [
                'success' => false,
                'message' => 'Cannot parse patient control number: ' . $pcn,
                'action' => 'none',
            ];
        }

        $pid = $parsed['pid'];
        $encounter = $parsed['encounter'];

        // Check encounter exists
        $feRow = QueryUtils::querySingleRow(
            "SELECT pid, encounter FROM form_encounter WHERE pid = ? AND encounter = ?",
            [$pid, $encounter]
        );
        if ($feRow === [] || $feRow === false) {
            return [
                'success' => false,
                'message' => 'Encounter not found in OpenEMR: ' . $pcn,
                'action' => 'none',
            ];
        }

        // Get current OE claim status
        $claimRow = QueryUtils::querySingleRow(
            "SELECT status, bill_process, payer_id, payer_type FROM claims " .
            "WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1",
            [$pid, $encounter]
        );

        if ($claimRow === [] || $claimRow === false) {
            return [
                'success' => false,
                'message' => 'No claim record found in OpenEMR for: ' . $pcn,
                'action' => 'none',
            ];
        }

        $currentStatus = TypeCoerce::asInt($claimRow['status'] ?? 0);
        $payerId = TypeCoerce::asInt($claimRow['payer_id'] ?? 0);
        $payerType = TypeCoerce::asInt($claimRow['payer_type'] ?? 0);

        $statusId = $claimData['statusId'];
        $payerAcceptanceStatusId = $claimData['payerAcceptanceStatusId'];
        $statusName = $claimData['statusName'];
        $errorMessage = $claimData['errorMessage'] ?? '';

        // Determine if ClaimRev says this claim is rejected/denied
        $isRejected = in_array($statusId, [10, 16, 17]) || $payerAcceptanceStatusId === 3;

        if ($isRejected) {
            if ($currentStatus === 7) {
                return [
                    'success' => true,
                    'message' => 'Already marked as denied in OpenEMR',
                    'action' => 'none',
                ];
            }

            // Build a process_file string with the error/status info
            $processFile = $statusName;
            if ($errorMessage !== '') {
                $processFile = $errorMessage;
            }

            BillingUtilities::updateClaim(
                true,           // new version
                $pid,
                $encounter,
                $payerId,
                $payerType,
                7,              // status = denied
                0,              // bill_process = open
                $processFile
            );

            ClaimTrackingService::logEvent(
                $pid,
                $encounter,
                $payerType,
                ClaimTrackingService::EVENT_REJECTED,
                ClaimTrackingService::SOURCE_CLAIMREV,
                statusCode: (string) $statusId,
                statusDescription: $statusName,
                detailText: $processFile,
            );

            return [
                'success' => true,
                'message' => 'Claim marked as denied: ' . $processFile,
                'action' => 'denied',
            ];
        }

        // Accepted by payer — OE should already be status=2 (billed)
        if ($payerAcceptanceStatusId === 4) {
            if ($currentStatus === 2) {
                return [
                    'success' => true,
                    'message' => 'Claim is accepted and already marked as billed in OpenEMR',
                    'action' => 'none',
                ];
            }

            return [
                'success' => true,
                'message' => 'Claim is accepted by payer — OpenEMR status is ' . $currentStatus . ', no sync needed',
                'action' => 'none',
            ];
        }

        return [
            'success' => true,
            'message' => 'ClaimRev status (' . $statusName . ') does not require an OpenEMR update',
            'action' => 'none',
        ];
    }

    /**
     * Requeue a claim for billing in OpenEMR.
     *
     * Creates a new claim version with status=1 (unbilled) and bill_process=0 (open),
     * which puts it back in the billing queue for resubmission.
     *
     * @param string $patientControlNumber Format: "pid-encounter"
     * @return array{success: bool, message: string}
     */
    public static function requeueForBilling(string $patientControlNumber): array
    {
        $parsed = self::parsePcn($patientControlNumber);
        if ($parsed === null) {
            return [
                'success' => false,
                'message' => 'Cannot parse patient control number: ' . $patientControlNumber,
            ];
        }

        $pid = $parsed['pid'];
        $encounter = $parsed['encounter'];

        // Get current claim record
        $claimRow = QueryUtils::querySingleRow(
            "SELECT status, bill_process, payer_id, payer_type FROM claims " .
            "WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1",
            [$pid, $encounter]
        );

        if ($claimRow === [] || $claimRow === false) {
            return [
                'success' => false,
                'message' => 'No claim record found in OpenEMR',
            ];
        }

        $currentStatus = TypeCoerce::asInt($claimRow['status'] ?? 0);
        $payerId = TypeCoerce::asInt($claimRow['payer_id'] ?? 0);
        $payerType = TypeCoerce::asInt($claimRow['payer_type'] ?? 0);

        // Only requeue if denied (7) or billed (2) — doesn't make sense for unbilled (0/1)
        if ($currentStatus === 0 || $currentStatus === 1) {
            return [
                'success' => false,
                'message' => 'Claim is already unbilled/open — no requeue needed',
            ];
        }

        // Reset billing flags on the encounter
        BillingUtilities::reOpenEncounterForBilling($pid, $encounter);

        // Create new claim version as unbilled
        BillingUtilities::updateClaim(
            true,           // new version
            $pid,
            $encounter,
            $payerId,
            $payerType,
            1,              // status = unbilled (open for processing)
            0               // bill_process = open
        );

        ClaimTrackingService::logEvent(
            $pid,
            $encounter,
            $payerType,
            ClaimTrackingService::EVENT_REQUEUED,
            ClaimTrackingService::SOURCE_USER,
            statusDescription: 'Requeued for billing',
        );

        return [
            'success' => true,
            'message' => 'Claim requeued for billing (pid=' . $pid . ', encounter=' . $encounter . ')',
        ];
    }

    /**
     * Parse a patient control number into pid and encounter.
     *
     * @return array{pid: int, encounter: int}|null
     */
    private static function parsePcn(string $pcn): ?array
    {
        if ($pcn === '') {
            return null;
        }

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
}
