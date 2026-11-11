<?php

/**
 * RemoteDirectory over an SFTP session that is already logged in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use phpseclib3\Net\SFTP;

final readonly class SftpRemoteDirectory implements RemoteDirectory
{
    /**
     * NET_SFTP_TYPE_REGULAR: what rawlist() reports for a plain file.
     */
    private const TYPE_REGULAR = 1;

    public function __construct(private SFTP $sftp)
    {
    }

    public function listFiles(string $directory): ?array
    {
        $listing = $this->sftp->rawlist($directory);
        if (!is_array($listing)) {
            return null;
        }
        $files = [];
        foreach ($listing as $name => $attributes) {
            if (!is_array($attributes) || ($attributes['type'] ?? null) !== self::TYPE_REGULAR) {
                continue;
            }
            $size = $attributes['size'] ?? null;
            $modifiedAt = $attributes['mtime'] ?? null;
            if (!is_int($size)) {
                continue;
            }
            $files[] = new RemoteFile((string) $name, $size, is_int($modifiedAt) ? $modifiedAt : 0);
        }
        usort($files, static fn(RemoteFile $a, RemoteFile $b): int => strcmp($a->name, $b->name));
        return $files;
    }

    public function download(string $remotePath, string $localPath, int $maxBytes): bool
    {
        return $this->sftp->get($remotePath, $localPath, 0, $maxBytes) === true;
    }

    public function move(string $fromPath, string $toPath): bool
    {
        return $this->sftp->rename($fromPath, $toPath);
    }
}
