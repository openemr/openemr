<?php

/**
 * One direct_message_log row, as the Direct Message Log report shows it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class DirectMessageLogRecord
{
    /**
     * @param ?DirectMessageType $type null when msg_type holds a code the enum does not know
     * @param string $createdAt create_ts as the database formats it
     * @param ?DirectMessageLogStatus $status null when status holds a code the enum does not know
     * @param ?string $statusChangedAt status_ts as the database formats it
     */
    public function __construct(
        public int $id,
        public ?DirectMessageType $type,
        public string $createdAt,
        public string $sender,
        public string $recipient,
        public ?DirectMessageLogStatus $status,
        public ?string $statusChangedAt,
    ) {
    }

    /**
     * @param array<mixed> $row
     * @throws \UnexpectedValueException when a column is missing or has the wrong type
     */
    public static function fromRow(array $row): self
    {
        $id = $row['id'] ?? null;
        $type = $row['msg_type'] ?? null;
        $createdAt = $row['create_ts'] ?? null;
        $sender = $row['sender'] ?? null;
        $recipient = $row['recipient'] ?? null;
        $status = $row['status'] ?? null;
        $statusChangedAt = $row['status_ts'] ?? null;
        if (
            !(is_int($id) || (is_string($id) && ctype_digit($id)))
            || !is_string($type)
            || !is_string($createdAt)
            || !is_string($sender)
            || !is_string($recipient)
            || !is_string($status)
            || ($statusChangedAt !== null && !is_string($statusChangedAt))
        ) {
            throw new \UnexpectedValueException('direct_message_log row has a missing or malformed column');
        }
        return new self(
            id: (int) $id,
            type: DirectMessageType::tryFrom($type),
            createdAt: $createdAt,
            sender: $sender,
            recipient: $recipient,
            status: DirectMessageLogStatus::tryFrom($status),
            statusChangedAt: $statusChangedAt,
        );
    }

    /** The type's label, or null for a type the report leaves blank. */
    public function typeLabel(): ?string
    {
        return $this->type?->label();
    }

    /** The status's label, or null for a status the report leaves blank. */
    public function statusLabel(): ?string
    {
        return $this->status?->label();
    }
}
