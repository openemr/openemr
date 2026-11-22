<?php

/**
 * ReceivedFileStore on the site's documents folder.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use OpenEMR\Billing\EraFilenameValidator;
use OpenEMR\Billing\ParseERA;

/**
 * A remittance goes where the ERA posting screen keeps an uploaded one,
 * documents/era/<name>.edi, under the name the upload gives it, with the same
 * copy in EDI History. Claim status and acknowledgment files go to their EDI
 * History folders, where "Process" on the New Files tab finds them.
 *
 * A file already there is never replaced, and a file only appears under its
 * final name once all of it is written.
 */
final readonly class SiteReceivedFileStore implements ReceivedFileStore
{
    public function __construct(private string $siteDirectory)
    {
    }

    public function store(X12FileType $type, string $localPath, string $remoteName): StoreOutcome
    {
        $history = $this->siteDirectory . '/documents/edi/history/' . $type->value;
        if ($type !== X12FileType::Remittance835) {
            return $this->storeInHistory($localPath, $history, $remoteName);
        }

        $name = self::eraName($localPath);
        if ($name === null) {
            return new StoreOutcome(ReceivedStatus::Rejected, message: 'The 835 could not be read to name it');
        }
        $eraDirectory = $this->siteDirectory . '/documents/era';
        if (file_exists($eraDirectory . '/' . $name . '.edi')) {
            return match (self::sameContent($localPath, $eraDirectory . '/' . $name . '.edi')) {
                true => new StoreOutcome(ReceivedStatus::Duplicate, $name . '.edi', 'This file is already stored'),
                // Two remittances with one name: the posting screen can only hold one of them.
                false => new StoreOutcome(
                    ReceivedStatus::Rejected,
                    message: 'A different 835 of this name is already stored for posting',
                ),
                null => new StoreOutcome(ReceivedStatus::Failed, message: 'The stored 835 could not be read to compare it'),
            };
        }
        // The EDI History copy (the posting screen makes one on upload) goes first. If it
        // cannot be written nothing is in documents/era yet, so the file is reported as
        // failed and the next run stores both.
        $historyCopy = $this->placeOnce($localPath, $history, $name . '.835', 0400);
        if ($historyCopy->status === ReceivedStatus::Failed) {
            return $historyCopy;
        }
        $sameAsHistory = $historyCopy->status === ReceivedStatus::Duplicate
            ? self::sameContent($localPath, $history . '/' . $name . '.835')
            : true;
        if ($sameAsHistory === null) {
            // Not knowing is not a mismatch: leave the file to be tried again.
            return new StoreOutcome(ReceivedStatus::Failed, message: 'The EDI History copy could not be read to compare it');
        }
        if (!$sameAsHistory) {
            // Another remittance already holds this name in EDI History. Storing this one for
            // posting would leave the two copies of the name disagreeing.
            return new StoreOutcome(
                ReceivedStatus::Rejected,
                message: 'EDI History already holds a different 835 of this name',
            );
        }
        return $this->placeOnce($localPath, $eraDirectory, $name . '.edi', 0600);
    }

    /**
     * Claim status and acknowledgment files keep the partner's file name. EDI History has
     * one folder per kind for every partner and for uploads, so a name can already be
     * taken by a different file; that file is then stored with part of its checksum in
     * the name instead of being dropped.
     */
    private function storeInHistory(string $localPath, string $history, string $remoteName): StoreOutcome
    {
        $name = self::safeName($remoteName);
        if ($name === null) {
            return new StoreOutcome(ReceivedStatus::Rejected, message: 'The remote file name cannot be used');
        }
        $outcome = $this->placeOnce($localPath, $history, $name, 0400);
        if ($outcome->status !== ReceivedStatus::Duplicate) {
            return $outcome;
        }
        $same = self::sameContent($localPath, $history . '/' . $name);
        if ($same === null) {
            return new StoreOutcome(ReceivedStatus::Failed, message: 'The stored file could not be read to compare it');
        }
        if ($same) {
            return $outcome;
        }
        $checksum = substr((string) hash_file('sha256', $localPath), 0, 8);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $stem = $extension === '' ? $name : substr($name, 0, -strlen($extension) - 1);
        return $this->placeOnce($localPath, $history, $stem . '-' . $checksum . ($extension === '' ? '' : '.' . $extension), 0400);
    }

    /**
     * The name the ERA posting screen gives an uploaded 835:
     * <GS date>_<ISA control number>_<payer id>, or null when the file has no
     * claim payment to take it from.
     *
     * It matches the upload (`eob_search_era_callback()` in interface/billing/sl_eob_search.php)
     * for every payer id the upload can name. One case differs on purpose: a payer id of
     * several zeros ("000"), which the upload keeps and then trims to nothing, giving a name
     * the posting screen refuses. Here it falls back to the ISA sender like an empty one.
     */
    public static function eraName(string $path): ?string
    {
        $name = '';
        ob_start();
        try {
            ParseERA::parseERA($path, static function (array $out) use (&$name): void {
                $text = static fn(string $key): string => is_string($out[$key] ?? null) ? $out[$key] : '';
                // As on upload, a payer id that is empty or all zeros falls back to the sender.
                $payer = ltrim($text('payer_id'), '0') !== '' ? $text('payer_id') : $text('isa_sender_id');
                $name = $text('gs_date') . '_' . ltrim($text('isa_control_number'), '0') . '_' . ltrim($payer, '0');
            });
        } finally {
            ob_end_clean();
        }
        // The parser's complaints about the rest of the file are for the posting screen to
        // show, as they are after an upload; all that is needed here is the name.
        return EraFilenameValidator::sanitize($name);
    }

    /**
     * @return ?string the name with anything but letters, digits, dot, dash and underscore replaced
     */
    public static function safeName(string $remoteName): ?string
    {
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $remoteName);
        if (!is_string($name) || $name === '' || trim($name, '._') === '' || str_starts_with($name, '.')) {
            return null;
        }
        return substr($name, 0, 200);
    }

    /**
     * @return ?bool null when either file could not be read
     */
    private static function sameContent(string $pathA, string $pathB): ?bool
    {
        $hashA = @hash_file('sha256', $pathA);
        $hashB = @hash_file('sha256', $pathB);
        if ($hashA === false || $hashB === false) {
            return null;
        }
        return $hashA === $hashB;
    }

    /**
     * Copies the file to $directory/$name unless something is already there. The copy is
     * written under a temporary name in the site's documents/temp folder and linked into
     * place when complete, so a run that is cut short never leaves part of a file under
     * the final name.
     */
    private function placeOnce(string $localPath, string $directory, string $name, int $mode): StoreOutcome
    {
        $staging = $this->siteDirectory . '/documents/temp';
        foreach ([$directory, $staging] as $needed) {
            if (!is_dir($needed) && !@mkdir($needed, 0755, true) && !is_dir($needed)) {
                return new StoreOutcome(ReceivedStatus::Failed, message: 'A folder for the file could not be created');
            }
        }
        $target = $directory . '/' . $name;
        if (file_exists($target)) {
            return new StoreOutcome(ReceivedStatus::Duplicate, $name, 'A file of this name is already stored');
        }
        $staged = tempnam($staging, 'x12r');
        if ($staged === false) {
            return new StoreOutcome(ReceivedStatus::Failed, message: 'The file could not be written');
        }
        try {
            if (!@copy($localPath, $staged) || filesize($staged) !== filesize($localPath) || !chmod($staged, $mode)) {
                return new StoreOutcome(ReceivedStatus::Failed, message: 'The file could not be written');
            }
            // link() refuses a name that exists; rename() is for a filesystem without hard links.
            if (@link($staged, $target) || (!file_exists($target) && @rename($staged, $target))) {
                return new StoreOutcome(ReceivedStatus::Stored, $name);
            }
            return file_exists($target)
                ? new StoreOutcome(ReceivedStatus::Duplicate, $name, 'A file of this name is already stored')
                : new StoreOutcome(ReceivedStatus::Failed, message: 'The file could not be written');
        } finally {
            if (is_file($staged)) {
                unlink($staged);
            }
        }
    }
}
