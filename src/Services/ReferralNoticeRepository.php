<?php

/**
 * The notices referral recipients got (#15137): referral_notice, one row each time a recipient was
 * told about a Task, or couldn't be.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\DirectMessageLogStatus;
use OpenEMR\Common\Uuid\UuidRegistry;

class ReferralNoticeRepository
{
    /**
     * @param string $taskUuid the Task the recipient was told about
     * @param ?string $messageId a Direct notice's id in the message log
     * @throws SqlQueryException
     */
    public function record(
        int $referralId,
        string $taskUuid,
        ReferralNoticeOutcome $outcome,
        ?string $address,
        ?string $messageId,
        ?string $detail,
        ?int $userId,
        \DateTimeImmutable $at,
    ): void {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO referral_notice (transaction_id, task_uuid, outcome, address, msg_id, detail, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $referralId,
                UuidRegistry::uuidToBytes($taskUuid),
                $outcome->value,
                $address,
                $messageId,
                $detail === null ? null : mb_substr($detail, 0, 511),
                $userId,
                $at->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * The referral's latest notice, with a Direct notice's delivery as the message log has it, or
     * null when its recipient was never told.
     *
     * @throws SqlQueryException
     */
    public function latest(int $referralId): ?ReferralNotice
    {
        $row = QueryUtils::querySingleRow(
            "SELECT n.outcome, n.address, n.detail, n.created_at, log.status, log.status_info
                FROM referral_notice AS n
                LEFT JOIN direct_message_log AS log ON log.msg_type = 'S' AND log.msg_id = n.msg_id AND n.msg_id IS NOT NULL
                WHERE n.transaction_id = ?
                ORDER BY n.id DESC LIMIT 1",
            [$referralId]
        );
        if (!is_array($row)) {
            return null;
        }
        $outcome = is_string($row['outcome'] ?? null) ? ReferralNoticeOutcome::tryFrom($row['outcome']) : null;
        $createdAt = $row['created_at'] ?? null;
        if ($outcome === null || !is_string($createdAt)) {
            return null;
        }
        $text = static fn(string $column): ?string => is_string($row[$column] ?? null) && $row[$column] !== '' ? $row[$column] : null;
        $status = $text('status');
        return new ReferralNotice(
            outcome: $outcome,
            createdAt: $createdAt,
            address: $text('address'),
            detail: $text('detail'),
            deliveryStatus: $status === null ? null : DirectMessageLogStatus::tryFrom($status),
            deliveryInfo: $text('status_info'),
        );
    }

    /**
     * The Direct messages sent with the referral whose delivery failed, newest first, other than its
     * notices, which latest() reports: a C-CDA staff sent from the send form, for instance.
     *
     * @return list<array{recipient: string, sent_at: string}>
     * @throws SqlQueryException
     */
    public function failedDirectSends(int $referralId): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT log.recipient, log.create_ts FROM direct_message_log AS log
                WHERE log.msg_type = 'S' AND log.status = ? AND log.referral_id = ?
                    AND NOT EXISTS (SELECT 1 FROM referral_notice AS n WHERE n.transaction_id = log.referral_id AND n.msg_id = log.msg_id)
                ORDER BY log.id DESC",
            [DirectMessageLogStatus::Failed->value, $referralId]
        );
        $sends = [];
        foreach ($rows as $row) {
            if (is_string($row['recipient'] ?? null) && is_string($row['create_ts'] ?? null)) {
                $sends[] = ['recipient' => $row['recipient'], 'sent_at' => $row['create_ts']];
            }
        }
        return $sends;
    }

    /**
     * The Direct replies filed with the referral (PhiMailReceiver matched them to a message sent with
     * it), newest first.
     *
     * @return list<ReferralDirectReply>
     * @throws SqlQueryException
     */
    public function directReplies(int $referralId): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT id, create_ts, sender, note_id FROM direct_message_log WHERE msg_type = 'R' AND referral_id = ? ORDER BY id DESC",
            [$referralId]
        );
        $replies = [];
        foreach ($rows as $row) {
            if (is_numeric($row['id'] ?? null) && is_string($row['create_ts'] ?? null) && is_string($row['sender'] ?? null)) {
                $replies[] = new ReferralDirectReply(
                    (int)$row['id'],
                    $row['create_ts'],
                    $row['sender'],
                    is_numeric($row['note_id'] ?? null) ? (int)$row['note_id'] : null,
                );
            }
        }
        return $replies;
    }

    /**
     * The addresses a referral's recipient (Refer To's address book entry) can be told at: its Direct
     * address and its email address, each trimmed, or null when the referral names no one. Also the
     * referral's patient.
     *
     * @return ?array{patient_id: int, direct: string, email: string}
     * @throws SqlQueryException
     */
    public function recipientAddresses(int $referralId): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT t.pid, u.email_direct, u.email
                FROM transactions AS t
                JOIN lbt_data AS recipient ON recipient.form_id = t.id AND recipient.field_id = 'refer_to'
                JOIN users AS u ON u.id = recipient.field_value
                WHERE t.id = ?",
            [$referralId]
        );
        if (!is_array($row) || !is_numeric($row['pid'] ?? null)) {
            return null;
        }
        return [
            'patient_id' => (int)$row['pid'],
            'direct' => is_string($row['email_direct'] ?? null) ? trim($row['email_direct']) : '',
            'email' => is_string($row['email'] ?? null) ? trim($row['email']) : '',
        ];
    }
}
