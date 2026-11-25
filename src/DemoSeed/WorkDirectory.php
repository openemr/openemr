<?php

/**
 * The loader's work directory, where the Synthea jar, Synthea's output and
 * the patient manifest go.
 *
 * The loader runs the jar it finds there, so the directory must be one only
 * the user running the loader can write to: a directory under /tmp that
 * someone else made first is refused, and so is one under a directory
 * someone else could rename it out of. A run also holds a lock on it, so two
 * runs can't share Synthea's output.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final readonly class WorkDirectory
{
    /**
     * @param resource $lock held until this object goes away
     */
    private function __construct(public string $path, private mixed $lock)
    {
    }

    /**
     * Makes the directory (private to this user) if it isn't there, checks
     * that only this user can write to it, and locks it.
     *
     * @param string $currentDirectory what a relative $path is relative to
     * @throws WorkDirectoryException
     */
    public static function open(string $path, string $currentDirectory, Filesystem $filesystem): self
    {
        $path = Path::makeAbsolute($path, $currentDirectory);
        if (!file_exists($path) && !is_link($path)) {
            $filesystem->mkdir($path, 0700);
            $filesystem->chmod($path, 0700);
        }
        // From here on the resolved path, so a symlink above the directory
        // can't be pointed elsewhere between the checks and the run.
        $path = self::assertPrivateDirectory($path);

        $lock = fopen($path . '/.lock', 'c');
        if ($lock === false) {
            throw new WorkDirectoryException('Could not create the lock file in ' . $path);
        }
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            throw new WorkDirectoryException('Another run of the loader is using ' . $path);
        }
        return new self($path, $lock);
    }

    public function __destruct()
    {
        flock($this->lock, LOCK_UN);
        fclose($this->lock);
    }

    public function file(string $name): string
    {
        return $this->path . '/' . $name;
    }

    /**
     * Checks that a file in this directory belongs to the directory's owner
     * and that no one else can write to it.
     *
     * @throws WorkDirectoryException
     */
    public function assertPrivateFile(string $file): void
    {
        clearstatcache(true, $file);
        if (is_link($file) || !is_file($file)) {
            throw new WorkDirectoryException($file . ' is not a regular file');
        }
        $permissions = fileperms($file);
        if ($permissions === false || ($permissions & 0o022) !== 0) {
            throw new WorkDirectoryException($file . ' can be changed by other users; remove it or run chmod go-w on it');
        }
        if (fileowner($file) !== fileowner($this->path)) {
            throw new WorkDirectoryException($file . ' belongs to another user; remove it');
        }
    }

    /**
     * @return string the directory with symlinks above it resolved
     * @throws WorkDirectoryException
     */
    private static function assertPrivateDirectory(string $path): string
    {
        clearstatcache(true, $path);
        if (is_link($path) || !is_dir($path)) {
            throw new WorkDirectoryException($path . ' is not a directory');
        }
        $permissions = fileperms($path);
        if ($permissions === false || ($permissions & 0o022) !== 0) {
            throw new WorkDirectoryException(
                $path . ' can be written by other users; use --work-dir with a directory only you can write to',
            );
        }
        // tempnam() gives a path under the resolved directory, e.g. under
        // /private/tmp for /tmp on macOS.
        $resolved = realpath($path);
        if ($resolved === false) {
            throw new WorkDirectoryException($path . ' is not a directory');
        }
        // Without ext-posix there's no portable way to ask for this process's
        // user id, so compare the directory's owner with a file made here.
        $probe = @tempnam($resolved, '.owner-');
        if ($probe === false || dirname($probe) !== $resolved) {
            if ($probe !== false) {
                unlink($probe);
            }
            throw new WorkDirectoryException('Can not write to ' . $path);
        }
        $user = fileowner($probe);
        unlink($probe);
        if ($user === false || fileowner($resolved) !== $user) {
            throw new WorkDirectoryException(
                $path . ' belongs to another user; use --work-dir with a directory of your own',
            );
        }
        self::assertSafeAncestors($path, $resolved, $user);
        return $resolved;
    }

    /**
     * Someone who can change a directory above the work directory could
     * rename the work directory and put another in its place, with another
     * jar in it. So each one must belong to root or this user, and if others
     * can write to it, it must be sticky (like /tmp), so they can't rename
     * what isn't theirs.
     *
     * @throws WorkDirectoryException
     */
    private static function assertSafeAncestors(string $path, string $resolved, int $user): void
    {
        $directory = $resolved;
        while (($parent = dirname($directory)) !== $directory) {
            $directory = $parent;
            $owner = fileowner($directory);
            $permissions = fileperms($directory);
            if ($owner === false || $permissions === false) {
                throw new WorkDirectoryException('Can not check ' . $directory . ', which holds ' . $path);
            }
            if ($owner !== 0 && $owner !== $user) {
                throw new WorkDirectoryException(
                    $directory . ' belongs to another user, who could replace ' . $path
                    . '; use --work-dir with a directory of your own',
                );
            }
            if (($permissions & 0o022) !== 0 && ($permissions & 0o1000) === 0) {
                throw new WorkDirectoryException(
                    $directory . ' can be written by other users, who could replace ' . $path
                    . '; use --work-dir with a directory of your own',
                );
            }
        }
    }
}
