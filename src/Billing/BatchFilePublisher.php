<?php

/**
 * Publish one claim batch only after its full contents are on disk.
 *
 * The batch name appears after the bytes are written and synced. A
 * completion note is written second, and the directory entry is synced
 * after the rename and after that note. A retry treats the batch as sent
 * only when that note matches the file's size.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final class BatchFilePublisher
{
    /**
     * Replace the batch with this content.
     *
     * A batch that is already complete is left alone. A failure after this
     * run renames its own file removes that file and its completion note.
     */
    public static function publish(string $directory, string $filename, string $content): bool
    {
        if ($content === '' || !self::nameIsSafe($filename) || $directory === '') {
            return false;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        $temporary = $final . '.partial';
        $note = $final . '.complete';
        $marker = $final . '.publishing';
        // An existing file is left in place, whether or not its note is valid.
        // A publishing marker belongs to an attempt that has not finished.
        if (
            is_link($final) || is_link($temporary) || is_link($note) || is_link($marker)
            || is_file($final) || is_file($marker) || self::isPublished($directory, $filename)
        ) {
            return false;
        }
        $handle = fopen($temporary, 'wb');
        if ($handle === false) {
            return false;
        }

        $written = fwrite($handle, $content);
        $synced = fflush($handle);
        if (function_exists('fsync')) {
            $synced = fsync($handle) && $synced;
        }
        fclose($handle);
        if ($written !== strlen($content) || $synced !== true || !is_file($temporary)) {
            self::remove($temporary);

            return false;
        }

        $size = filesize($temporary);
        if ($size !== strlen($content)) {
            self::remove($temporary);

            return false;
        }

        // The marker is written before the rename, so a crash in between is
        // not mistaken for an older batch that has no completion note.
        if (!self::writeMarker($marker) || !self::syncDirectory($directory, $filename . '.publishing')) {
            self::remove($temporary);
            self::remove($marker);

            return false;
        }
        if (!rename($temporary, $final)) {
            self::remove($temporary);
            self::remove($marker);

            return false;
        }

        // The new name has to reach disk before the completion note does.
        // A later failure removes this run's file so the name is not left behind.
        if (
            !self::syncDirectory($directory, $filename)
            || !self::writeCompletion($final, $size)
            || !self::syncDirectory($directory, $filename . '.complete')
        ) {
            self::discard($directory, $filename);

            return false;
        }

        self::remove($marker);

        return true;
    }

    /**
     * Sync the directory entry for one file in the batch directory.
     */
    private static function syncDirectory(string $directory, string $entry): bool
    {
        if (!is_file($directory . DIRECTORY_SEPARATOR . $entry)) {
            return false;
        }

        if (!function_exists('fsync')) {
            return true;
        }

        $handle = fopen($directory, 'r');
        if ($handle === false) {
            // Windows has no directory stream. Elsewhere the entry was not synced.
            return PHP_OS_FAMILY === 'Windows';
        }

        $synced = fsync($handle);
        fclose($handle);

        return $synced;
    }

    /**
     * Remove a batch this run published after the claim stopped naming it.
     */
    public static function discard(string $directory, string $filename): void
    {
        if (!self::nameIsSafe($filename) || $directory === '') {
            return;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        self::remove($final);
        self::remove($final . '.partial');
        self::remove($final . '.complete');
        self::remove($final . '.publishing');
    }

    /**
     * True when this name was claimed by a publication that did not finish.
     */
    public static function hasPublishingMarker(string $directory, string $filename): bool
    {
        if (!self::nameIsSafe($filename) || $directory === '') {
            return false;
        }

        $marker = $directory . DIRECTORY_SEPARATOR . $filename . '.publishing';

        return is_file($marker) && !is_link($marker);
    }

    /**
     * Move an interrupted batch aside so its name can be written again.
     *
     * The original bytes stay next to the directory as a `.interrupted` file.
     * False leaves the downloadable name in place.
     */
    public static function quarantineInterrupted(string $directory, string $filename): bool
    {
        if (!self::hasPublishingMarker($directory, $filename)) {
            return false;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        if (is_link($final)) {
            return false;
        }
        if (is_file($final)) {
            $held = $final . '.interrupted';
            if (is_file($held) || is_link($held) || !rename($final, $held)) {
                return false;
            }
        }
        self::remove($final . '.publishing');
        self::remove($final . '.partial');
        self::remove($final . '.complete');

        return !is_file($final) && !self::hasPublishingMarker($directory, $filename);
    }

    /**
     * Whether this stored name may be downloaded.
     *
     * A matching completion note is required once one exists. A publishing
     * marker means the batch was interrupted and is not served. A file with
     * neither is an older batch or a validation file.
     */
    public static function downloadAllowed(string $directory, string $filename): bool
    {
        if (!self::nameIsSafe($filename) || $directory === '') {
            return false;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($final) || is_link($final) || self::hasPublishingMarker($directory, $filename)) {
            return false;
        }
        $note = $final . '.complete';
        if (!is_file($note)) {
            return true;
        }

        return self::isPublished($directory, $filename);
    }

    /**
     * True when the batch name is present and its completion note matches its size.
     */
    public static function isPublished(string $directory, string $filename): bool
    {
        if (!self::nameIsSafe($filename) || $directory === '') {
            return false;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        $size = is_file($final) ? filesize($final) : false;
        if (!is_int($size) || $size < 1) {
            return false;
        }

        $note = $final . '.complete';
        if (!is_file($note)) {
            return false;
        }

        $recorded = file_get_contents($note);

        return is_string($recorded) && trim($recorded) === (string) $size;
    }

    private static function nameIsSafe(string $filename): bool
    {
        return $filename !== ''
            && !str_contains($filename, '/')
            && !str_contains($filename, '\\')
            && !str_contains($filename, '..');
    }

    private static function writeMarker(string $path): bool
    {
        if (is_dir($path) || is_link($path)) {
            return false;
        }
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            return false;
        }
        $written = fwrite($handle, '1');
        $synced = fflush($handle);
        if (function_exists('fsync')) {
            $synced = fsync($handle) && $synced;
        }
        fclose($handle);
        if ($written !== 1 || $synced !== true) {
            self::remove($path);

            return false;
        }

        return true;
    }

    private static function writeCompletion(string $final, int $size): bool
    {
        $note = $final . '.complete';
        if (is_dir($note)) {
            return false;
        }
        $handle = fopen($note, 'wb');
        if ($handle === false) {
            return false;
        }

        $text = (string) $size;
        $written = fwrite($handle, $text);
        $synced = fflush($handle);
        if (function_exists('fsync')) {
            $synced = fsync($handle) && $synced;
        }
        fclose($handle);
        if ($written !== strlen($text) || $synced !== true) {
            self::remove($note);

            return false;
        }

        return self::isPublished(dirname($final), basename($final));
    }

    private static function remove(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
