<?php

/**
 * AutoPostLog in x12_remote_received.auto_post_status and the era_auto_post_check and era_auto_post_claim tables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\X12FileType;
use OpenEMR\Common\Database\QueryUtils;

final class AutoPostRepository implements AutoPostLog
{
    public function pending(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT r.`id`, r.`stored_name`, p.`x12_era_auto_post_user` AS post_user'
            . ' FROM `x12_remote_received` AS r'
            . ' JOIN `x12_partners` AS p ON p.`id` = r.`x12_partner_id`'
            . ' JOIN `users` AS u ON u.`id` = p.`x12_era_auto_post_user` AND u.`active` = 1'
            . " WHERE r.`status` = ? AND r.`file_type` = ? AND r.`auto_post_status` = ? AND r.`stored_name` != ''"
            . ' ORDER BY r.`id`',
            [ReceivedStatus::Stored->value, X12FileType::Remittance835->value, AutoPostFileStatus::Pending->value]
        );
        $pending = [];
        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            $name = $row['stored_name'] ?? null;
            $user = $row['post_user'] ?? null;
            if (is_numeric($id) && is_string($name) && is_numeric($user)) {
                $pending[] = new PendingRemittance((int) $id, $name, (int) $user);
            }
        }
        return $pending;
    }

    public function take(PendingRemittance $file): bool
    {
        // The user is checked again here, in the statement that takes the file: it may have
        // been taken off the partner or made inactive since the run listed its files.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `x12_remote_received` AS r'
            . ' JOIN `x12_partners` AS p ON p.`id` = r.`x12_partner_id`'
            . ' JOIN `users` AS u ON u.`id` = p.`x12_era_auto_post_user` AND u.`active` = 1'
            . ' SET r.`auto_post_status` = ? WHERE r.`id` = ? AND r.`auto_post_status` = ? AND u.`id` = ?',
            [AutoPostFileStatus::Running->value, $file->receivedId, AutoPostFileStatus::Pending->value, $file->postUser]
        );
        $taken = QueryUtils::affectedRows();
        return is_numeric($taken) && (int) $taken === 1;
    }

    public function finish(int $receivedId, AutoPostFileStatus $status): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `x12_remote_received` SET `auto_post_status` = ? WHERE `id` = ?',
            [$status->value, $receivedId]
        );
    }

    public function record(int $receivedId, int $sequence, CheckRecord $check, \DateTimeImmutable $at): void
    {
        $remitted = $check->check;
        $checkId = QueryUtils::sqlInsert(
            'INSERT INTO `era_auto_post_check` (`received_id`, `sequence`, `check_number`, `check_date`, `payer_name`,'
            . ' `payer_id`, `check_amount`, `claim_total`, `plb_total`, `plb_reasons`, `status`, `hold_reason`,'
            . ' `session_id`, `processed_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $receivedId,
                $sequence,
                mb_substr($remitted->checkNumber, 0, 255),
                substr($remitted->checkDate, 0, 8),
                mb_substr($remitted->payerName, 0, 255),
                ctype_digit($check->payerId) ? (int) $check->payerId : 0,
                $remitted->amount,
                $remitted->claimTotal(),
                $remitted->plbTotal,
                mb_substr(implode(',', $remitted->plbReasons), 0, 255),
                $check->status->value,
                $check->holdReason->value ?? '',
                $check->sessionId,
                $at->format('Y-m-d H:i:s'),
            ]
        );
        foreach ($check->claims as $index => $claim) {
            $payment = $claim->claim;
            QueryUtils::sqlInsert(
                'INSERT INTO `era_auto_post_claim` (`check_id`, `sequence`, `our_claim_id`, `payer_claim_id`,'
                . ' `patient_name`, `pid`, `encounter`, `payer_level`, `claim_status_code`, `outcome`, `reasons`,'
                . ' `claim_payment`, `payments_posted`, `adjustments_posted`, `charges_added`, `level_closed`,'
                . ' `secondary_queued`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $checkId,
                    $index + 1,
                    mb_substr($payment->ourClaimId, 0, 255),
                    mb_substr($payment->payerClaimId, 0, 255),
                    mb_substr(trim($payment->patientFirstName . ' ' . $payment->patientLastName), 0, 255),
                    $claim->pid,
                    $claim->encounter,
                    $claim->level,
                    substr($payment->claimStatusCode, 0, 8),
                    $claim->outcome->value,
                    mb_substr(implode(',', $claim->reasons), 0, 255),
                    $payment->paymentAmount,
                    $claim->paymentsPosted,
                    $claim->adjustmentsPosted,
                    $claim->chargesAdded,
                    $claim->levelClosed ? 1 : 0,
                    $claim->secondaryQueued ? 1 : 0,
                ]
            );
        }
    }
}
