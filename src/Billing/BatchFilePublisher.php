<?php

/**
 * Publish one claim batch only after its full contents are on disk.
 *
 * The batch name appears after the bytes are written and synced. A
 * completion note is written second. A retry treats the batch as sent
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
     * Replace the batch with this content. False leaves the previous file in place.
     */
    public static function publish(string $directory, string $filename, string $content): bool
    {
        if ($content === '' || !self::nameIsSafe($filename) || $directory === '') {
            return false;
        }

        $final = $directory . DIRECTORY_SEPARATOR . $filename;
        $temporary = $final . '.partial';
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
        if ($size !== strlen($content) || !rename($temporary, $final)) {
            self::remove($temporary);

            return false;
        }

        return self::writeCompletion($final, $size);
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

    private static function writeCompletion(string $final, int $size): bool
    {
        $note = $final . '.complete';
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
