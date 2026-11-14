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
    ): void {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `x12_remote_received` (`remote_key`, `x12_partner_id`, `remote_dir`, `remote_name`,'
            . ' `remote_size`, `remote_mtime`, `file_type`, `status`, `stored_name`, `message`, `received_at`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            . ' ON DUPLICATE KEY UPDATE `file_type` = VALUES(`file_type`), `status` = VALUES(`status`),'
            . ' `stored_name` = VALUES(`stored_name`),'
            . ' `message` = VALUES(`message`), `received_at` = VALUES(`received_at`)',
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
