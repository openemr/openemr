<?php

/**
 * The record of files seen in remote folders.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

interface ReceivedFileLog
{
    /**
     * Whether this file (same partner, folder, name, size and modified time)
     * was already stored, found to be a duplicate, or rejected.
     */
    public function isSettled(int $partnerId, string $folder, RemoteFile $file): bool;

    /**
     * @param ?X12FileType $type the kind of file it turned out to be; null when that was not established
     */
    public function record(
        int $partnerId,
        string $folder,
        RemoteFile $file,
        ?X12FileType $type,
        StoreOutcome $outcome,
        \DateTimeImmutable $at,
    ): void;
}
