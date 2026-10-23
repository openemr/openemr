<?php

/**
 * Process lock for the Weno prescription log sync.
 *
 * Every sync writes and then reads the same logsync.csv, so two syncs at once
 * (the widget's auto-sync and a Sync click, two tabs, or the WenoExchange
 * background service) overwrite each other's report and can import the same
 * prescriptions twice. Only one sync may hold this lock at a time.
 *
 * The lock is an flock() on a file next to logsync.csv. The OS drops it when
 * the holding process ends, so a sync that exits early cannot leave it stuck.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\WenoModule\Services;

final class SyncLock
{
    private const LOCK_FILE = 'logsync.lock';

    /** @var resource|null */
    private $handle = null;

    public function __construct(private readonly string $directory)
    {
    }

    /**
     * Lock for this site's Weno log directory.
     */
    public static function forSite(string $siteDir): self
    {
        return new self(rtrim($siteDir, '/\\') . '/documents/logs_and_misc/weno');
    }

    /**
     * Take the lock without waiting.
     *
     * @return bool true when this instance now holds the lock (or already did),
     *              false when another sync holds it or the lock file cannot be opened.
     */
    public function tryAcquire(): bool
    {
        if ($this->handle !== null) {
            return true;
        }
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            return false;
        }
        $handle = @fopen($this->lockPath(), 'c');
        if ($handle === false) {
            return false;
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }
        $this->handle = $handle;
        return true;
    }

    public function release(): void
    {
        if ($this->handle === null) {
            return;
        }
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }

    public function lockPath(): string
    {
        return $this->directory . '/' . self::LOCK_FILE;
    }

    public function __destruct()
    {
        $this->release();
    }
}
