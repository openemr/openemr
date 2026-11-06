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
     * Log a message received from the server.
     *
     * @throws SqlQueryException
     */
    public function recordReceived(string $messageId, string $sender, string $recipient, int $userId): void
    {
        $sql = <<<'SQL'
            INSERT INTO direct_message_log (msg_type, msg_id, sender, recipient, status, status_ts, user_id)
            VALUES ('R', ?, ?, ?, 'R', NOW(), ?)
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$messageId, $sender, $recipient, $userId], noLog: true);
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
