<?php

/**
 * ReceivedFileLog in the x12_remote_received table: one row per remote file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Common\Database\QueryUtils;

final class ReceivedFileRepository implements ReceivedFileLog
{
    public function isSettled(int $partnerId, string $folder, RemoteFile $file): bool
    {
        $status = QueryUtils::fetchSingleValue(
            'SELECT `status` FROM `x12_remote_received` WHERE `remote_key` = ?',
            'status',
            [self::remoteKey($partnerId, $folder, $file)]
        );
        return is_string($status) && (ReceivedStatus::tryFrom($status)?->isSettled() ?? false);
    }

    public function record(
        int $partnerId,
        string $folder,
        RemoteFile $file,
        ?X12FileType $type,
        StoreOutcome $outcome,
        \DateTimeImmutable $at,
        bool $postUnattended = false,
    ): void {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `x12_remote_received` (`remote_key`, `x12_partner_id`, `remote_dir`, `remote_name`,'
            . ' `remote_size`, `remote_mtime`, `file_type`, `status`, `stored_name`, `message`, `received_at`,'
            . ' `auto_post_status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            // A stored file stays stored, as the kind it was stored as: a second run that overlapped
            // the first reports it as a duplicate, or fails to download it, and neither must take
            // it out of the posting queue.
            . ' ON DUPLICATE KEY UPDATE `file_type` = IF(`status` = ?, `file_type`, VALUES(`file_type`)),'
            . ' `stored_name` = IF(`status` = ?, `stored_name`, VALUES(`stored_name`)),'
            . ' `status` = IF(`status` = ?, `status`, VALUES(`status`)),'
            . ' `message` = VALUES(`message`), `received_at` = VALUES(`received_at`),'
            // Only a file no posting run has touched: a retried download may now be stored.
            . " `auto_post_status` = IF(`auto_post_status` = '', VALUES(`auto_post_status`), `auto_post_status`)",
            [
                self::remoteKey($partnerId, $folder, $file),
                $partnerId,
                mb_substr($folder, 0, 255),
                mb_substr($file->name, 0, 255),
                $file->size,
                $file->modifiedAt,
                $type->value ?? '',
                $outcome->status->value,
                mb_substr($outcome->storedName, 0, 255),
                mb_substr($outcome->message, 0, 255),
                $at->format('Y-m-d H:i:s'),
                $postUnattended ? AutoPostFileStatus::Pending->value : '',
                ReceivedStatus::Stored->value,
                ReceivedStatus::Stored->value,
                ReceivedStatus::Stored->value,
            ]
        );
    }

    /**
     * One value to key on, so the unique index stays short whatever the names are.
     */
    public static function remoteKey(int $partnerId, string $folder, RemoteFile $file): string
    {
        return hash('sha256', implode("\0", [$partnerId, $folder, $file->name, $file->size, $file->modifiedAt]));
    }
}
