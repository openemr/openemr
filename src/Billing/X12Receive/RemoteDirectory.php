<?php

/**
 * An open connection to an X12 partner's file server.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

interface RemoteDirectory
{
    /**
     * @return ?list<RemoteFile> the regular files in $directory, or null when it cannot be listed
     */
    public function listFiles(string $directory): ?array;

    /**
     * Reads at most $maxBytes of the remote file into $localPath, so a file that is
     * larger than its listing said cannot fill the local disk.
     *
     * @return bool false when the file could not be read
     */
    public function download(string $remotePath, string $localPath, int $maxBytes): bool;

    public function move(string $fromPath, string $toPath): bool;
}
