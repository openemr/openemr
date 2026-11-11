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
 * A file already there is never replaced.
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
            $name = self::safeName($remoteName);
            if ($name === null) {
                return new StoreOutcome(ReceivedStatus::Rejected, message: 'The remote file name cannot be used');
            }
            return $this->copyOnce($localPath, $history, $name, 0400);
        }

        $name = self::eraName($localPath);
        if ($name === null) {
            return new StoreOutcome(ReceivedStatus::Rejected, message: 'The 835 could not be read to name it');
        }
        $eraDirectory = $this->siteDirectory . '/documents/era';
        if (file_exists($eraDirectory . '/' . $name . '.edi')) {
            return new StoreOutcome(ReceivedStatus::Duplicate, $name . '.edi', 'A file of this name is already stored');
        }
        // The EDI History copy (the posting screen makes one on upload) goes first. If it
        // cannot be written nothing is in documents/era yet, so the file is reported as
        // failed and the next run stores both. A copy already there is the same remittance.
        $historyCopy = $this->copyOnce($localPath, $history, $name . '.835', 0400);
        if ($historyCopy->status === ReceivedStatus::Failed) {
            return $historyCopy;
        }
        return $this->copyOnce($localPath, $eraDirectory, $name . '.edi', 0600);
    }

    /**
     * The name the ERA posting screen gives an uploaded 835:
     * <GS date>_<ISA control number>_<payer id>, or null when the file has no
     * claim payment to take it from.
     */
    public static function eraName(string $path): ?string
    {
        $name = '';
        ob_start();
        try {
            ParseERA::parseERA($path, static function (array $out) use (&$name): void {
                $text = static fn(string $key): string => is_string($out[$key] ?? null) ? $out[$key] : '';
                $payer = $text('payer_id') !== '' ? $text('payer_id') : $text('isa_sender_id');
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

    private function copyOnce(string $localPath, string $directory, string $name, int $mode): StoreOutcome
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return new StoreOutcome(ReceivedStatus::Failed, message: 'The folder for the file could not be created');
        }
        $target = $directory . '/' . $name;
        if (file_exists($target)) {
            return new StoreOutcome(ReceivedStatus::Duplicate, $name, 'A file of this name is already stored');
        }
        // "x" refuses to open a file that appeared since the check above.
        $out = @fopen($target, 'x');
        $in = @fopen($localPath, 'r');
        $copied = ($out !== false && $in !== false) ? stream_copy_to_stream($in, $out) : false;
        if ($in !== false) {
            fclose($in);
        }
        if ($out !== false) {
            fclose($out);
        }
        if ($copied === false || $copied !== filesize($localPath)) {
            if ($out !== false) {
                unlink($target);
            }
            return new StoreOutcome(ReceivedStatus::Failed, message: 'The file could not be written');
        }
        chmod($target, $mode);
        return new StoreOutcome(ReceivedStatus::Stored, $name);
    }
}
