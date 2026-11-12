<?php

/**
 * Reads and writes the direct_message_log table.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class DirectMessageLogRepository
{
    /**
     * Find the logged message with this server message ID.
     *
     * @throws SqlQueryException
     */
    public function findByMessageId(string $messageId): ?DirectMessageLogEntry
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT patient_id, user_id, create_ts, recipient FROM direct_message_log WHERE msg_id = ? LIMIT 1',
            [$messageId],
        );
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];

        $createdAt = $row['create_ts'] ?? null;
        $recipient = $row['recipient'] ?? null;
        if (!is_string($createdAt) || !is_string($recipient)) {
            throw new \UnexpectedValueException('direct_message_log row is missing create_ts or recipient');
        }
        return new DirectMessageLogEntry(
            patientId: self::nullableId($row['patient_id'] ?? null),
            userId: self::nullableId($row['user_id'] ?? null),
            createdAt: $createdAt,
            recipient: $recipient,
        );
    }

    /**
     * Record the delivery status the server reported for a sent message.
     *
     * @throws SqlQueryException
     */
    public function updateStatus(string $messageId, PhiMailDeliveryStatus $status, string $info): void
    {
        $sql = <<<'SQL'
            UPDATE direct_message_log SET status = ?, status_ts = NOW(), status_info = ?
            WHERE msg_type = 'S' AND msg_id = ?
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$status->value, $info, $messageId], noLog: true);
    }

    /**
     * Log a message the server accepted for delivery.
     *
     * @param ?int $referralId the referral (transactions.id) the message carries
     * @throws SqlQueryException
     */
    public function recordSent(string $messageId, string $sender, string $recipient, ?int $patientId, ?int $userId, ?int $referralId = null): void
    {
        $sql = <<<'SQL'
            INSERT INTO direct_message_log (msg_type, msg_id, sender, recipient, status, status_ts, patient_id, user_id, referral_id)
            VALUES ('S', ?, ?, ?, 'S', NOW(), ?, ?, ?)
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$messageId, $sender, $recipient, $patientId, $userId, $referralId], noLog: true);
    }

    /**
     * Log a message received from the server.
     *
     * @param ?int $patientId the patient a reply was filed to, when it answers a message sent about them
     * @param ?int $referralId the referral a reply was filed with, when the message it answers carried one
     * @param ?int $noteId the note the message was filed as
     * @throws SqlQueryException
     */
    public function recordReceived(
        string $messageId,
        string $sender,
        string $recipient,
        int $userId,
        ?int $patientId = null,
        ?int $referralId = null,
        ?int $noteId = null,
    ): void {
        $sql = <<<'SQL'
            INSERT INTO direct_message_log (msg_type, msg_id, sender, recipient, status, status_ts, user_id, patient_id, referral_id, note_id)
            VALUES ('R', ?, ?, ?, 'R', NOW(), ?, ?, ?, ?)
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$messageId, $sender, $recipient, $userId, $patientId, $referralId, $noteId], noLog: true);
    }

    /**
     * The sent message a received one answers, by the ids its In-Reply-To and References name
     * (DirectReplyHeaders), nearest first: the first that was sent from here about a patient, to the
     * address the reply comes from. Message ids aren't secret, so a message from anyone else that
     * names one isn't filed in the patient's chart. An id is looked up as it came and in angle
     * brackets, since the server's id may be logged either way.
     *
     * @param list<string> $messageIds without their angle brackets
     * @param string $sender the received message's sender, compared with the sent message's recipient ignoring case
     * @throws SqlQueryException
     */
    public function findReplyTarget(array $messageIds, string $sender): ?DirectReplyTarget
    {
        if ($messageIds === [] || trim($sender) === '') {
            return null;
        }
        $forms = [];
        foreach ($messageIds as $id) {
            $forms[] = $id;
            $forms[] = '<' . $id . '>';
        }
        $rows = QueryUtils::fetchRecordsNoLog(
            "SELECT msg_id, patient_id, referral_id FROM direct_message_log
                WHERE msg_type = 'S' AND patient_id IS NOT NULL AND LOWER(recipient) = ?
                    AND msg_id IN (" . implode(',', array_fill(0, count($forms), '?')) . ')',
            array_merge([strtolower(trim($sender))], $forms),
        );
        $found = [];
        foreach ($rows as $row) {
            $id = $row['msg_id'] ?? null;
            $patientId = self::nullableId($row['patient_id'] ?? null);
            if (is_string($id) && $patientId !== null && $patientId > 0) {
                $found[trim($id, '<>')] ??= new DirectReplyTarget($id, $patientId, self::nullableId($row['referral_id'] ?? null));
            }
        }
        foreach ($messageIds as $id) {
            if (isset($found[$id])) {
                return $found[$id];
            }
        }
        return null;
    }

    /**
     * The most recent messages, newest first.
     *
     * @return list<DirectMessageLogRecord>
     * @throws SqlQueryException
     */
    public function findLatest(int $limit): array
    {
        $sql = <<<'SQL'
            SELECT id, msg_type, create_ts, sender, recipient, status, status_ts FROM direct_message_log
            ORDER BY id DESC LIMIT ?
            SQL;
        return array_map(DirectMessageLogRecord::fromRow(...), QueryUtils::fetchRecords($sql, [$limit]));
    }

    /**
     * The messages whose ids fall in this inclusive range, newest first.
     *
     * @return list<DirectMessageLogRecord>
     * @throws SqlQueryException
     */
    public function findByIdRange(int $lowestId, int $highestId): array
    {
        $sql = <<<'SQL'
            SELECT id, msg_type, create_ts, sender, recipient, status, status_ts FROM direct_message_log
            WHERE id BETWEEN ? AND ? ORDER BY id DESC
            SQL;
        return array_map(DirectMessageLogRecord::fromRow(...), QueryUtils::fetchRecords($sql, [$lowestId, $highestId]));
    }

    private static function nullableId(mixed $value): ?int
    {
        return match (true) {
            $value === null => null,
            is_int($value) => $value,
            is_string($value) && ctype_digit($value) => (int) $value,
            default => throw new \UnexpectedValueException('direct_message_log row has a non-numeric id'),
        };
    }
}
