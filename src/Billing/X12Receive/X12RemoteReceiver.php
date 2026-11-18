<?php

/**
 * Downloads the files X12 partners have left in their remote folders.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

/**
 * For each partner and each remote folder it has set: list the folder, skip what an
 * earlier run settled, and download the rest one at a time (never more than the size
 * limit, nor more than the listing said). A file is stored only when all of it
 * arrived, it ends with a complete interchange, and it is one of the kinds of X12
 * file the folder is set for; a folder may serve several kinds. A stored file is moved
 * to the partner's worked folder when it has one; otherwise it stays on the remote
 * and is skipped next run by its record.
 *
 * A file or partner that fails with a runtime exception (the SFTP session dropping,
 * a database write failing) does not stop the run for the ones after it. A failure
 * while handling one file is recorded as that file failing; a failure of the record
 * itself (reading or writing x12_remote_received) ends that partner's turn, since the
 * partner's other files could not be recorded either, and the next partner is tried.
 * Other exceptions and PHP Errors are programming faults and are not caught.
 */
final readonly class X12RemoteReceiver
{
    /**
     * Largest file that is downloaded. X12 response files are a few megabytes at most;
     * anything bigger in a receive folder is not one, and would only fill the temporary
     * directory.
     */
    public const DEFAULT_MAX_FILE_BYTES = 52_428_800;

    public function __construct(
        private RemoteDirectoryOpener $opener,
        private ReceivedFileLog $log,
        private ReceivedFileStore $store,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private string $tempDirectory,
        private int $maxFileBytes = self::DEFAULT_MAX_FILE_BYTES,
    ) {
    }

    /**
     * @param list<ReceivePartner> $partners
     */
    public function receive(array $partners): ReceiveSummary
    {
        $summary = new ReceiveSummary();
        foreach ($partners as $partner) {
            $summary->partners++;
            try {
                $remote = $this->opener->open($partner);
                if ($remote === null) {
                    $summary->partnersUnreachable++;
                    continue;
                }
                foreach (self::typesByFolder($partner) as $folder => $types) {
                    $this->receiveFolder($partner, (string) $folder, $types, $remote, $summary);
                }
            } catch (\RuntimeException $e) {
                // The connection dropped or the record could not be written. What was not
                // recorded is tried again next run; the other partners still get their turn.
                $summary->partnersInterrupted++;
                $this->logger->error('X12 receive: the run for a partner was interrupted', [
                    'partner' => $partner->id,
                    'exception' => $e,
                ]);
            }
        }
        return $summary;
    }

    /**
     * @return array<string, non-empty-list<X12FileType>> the kinds of file each remote folder is set for
     */
    private static function typesByFolder(ReceivePartner $partner): array
    {
        $byFolder = [];
        foreach ($partner->folders as $typeValue => $folder) {
            $byFolder[$folder][] = X12FileType::from($typeValue);
        }
        return $byFolder;
    }

    /**
     * @param non-empty-list<X12FileType> $types
     */
    private function receiveFolder(
        ReceivePartner $partner,
        string $folder,
        array $types,
        RemoteDirectory $remote,
        ReceiveSummary $summary,
    ): void {
        $files = $remote->listFiles($folder);
        if ($files === null) {
            $summary->foldersUnreadable++;
            $this->logger->warning('X12 receive: could not list the remote folder', [
                'partner' => $partner->id,
                'folder' => $folder,
            ]);
            return;
        }
        foreach ($files as $file) {
            if ($this->log->isSettled($partner->id, $folder, $file)) {
                continue;
            }
            $type = null;
            try {
                [$type, $outcome] = $this->receiveFile($types, $folder, $file, $remote);
            } catch (\RuntimeException $e) {
                // One file that cannot be handled must not keep the files after it from
                // being received, on this run or the next.
                $this->logger->error('X12 receive: a file could not be received', [
                    'partner' => $partner->id,
                    'folder' => $folder,
                    'file' => $file->name,
                    'exception' => $e,
                ]);
                $outcome = new StoreOutcome(ReceivedStatus::Failed, message: 'An error interrupted this file');
            }
            $this->log->record($partner->id, $folder, $file, $type, $outcome, $this->clock->now());
            $summary->count($outcome->status);
            if ($outcome->status !== ReceivedStatus::Stored || $partner->workedFolder === '') {
                continue;
            }
            if ($remote->move(self::path($folder, $file->name), self::path($partner->workedFolder, $file->name))) {
                $summary->moved++;
            } else {
                $this->logger->warning('X12 receive: stored the file but could not move it on the remote', [
                    'partner' => $partner->id,
                    'folder' => $folder,
                    'file' => $file->name,
                ]);
            }
        }
    }

    /**
     * @param non-empty-list<X12FileType> $types the kinds of file the folder is set for
     * @return array{?X12FileType, StoreOutcome} the kind the file turned out to be, when that was established
     */
    private function receiveFile(array $types, string $folder, RemoteFile $file, RemoteDirectory $remote): array
    {
        if ($file->name === '' || str_contains($file->name, '/') || str_starts_with($file->name, '.')) {
            return [null, new StoreOutcome(ReceivedStatus::Rejected, message: 'The remote file name cannot be used')];
        }
        if ($file->size > $this->maxFileBytes) {
            return [null, new StoreOutcome(
                ReceivedStatus::Rejected,
                message: 'Larger than the ' . $this->maxFileBytes . ' byte limit for a received file',
            )];
        }
        $temp = tempnam($this->tempDirectory, 'x12r');
        if ($temp === false) {
            return [null, new StoreOutcome(ReceivedStatus::Failed, message: 'No temporary file could be created')];
        }
        try {
            // One byte more than the listing said, so a file that grew shows as a size mismatch.
            $downloaded = $remote->download(self::path($folder, $file->name), $temp, $file->size + 1);
            clearstatcache(true, $temp);
            if (!$downloaded || filesize($temp) !== $file->size) {
                return [null, new StoreOutcome(ReceivedStatus::Failed, message: 'The download did not complete')];
            }
            $head = (string) file_get_contents($temp, false, null, 0, X12Envelope::HEAD_LENGTH);
            $type = X12FileType::forGroupCode(X12Envelope::groupCode($head), $types);
            if ($type === null) {
                $labels = implode(', ', array_map(static fn(X12FileType $t): string => $t->label(), $types));
                return [null, new StoreOutcome(ReceivedStatus::Rejected, message: 'Not an X12 ' . $labels . ' file')];
            }
            $tail = (string) file_get_contents($temp, false, null, max(0, $file->size - X12Envelope::TAIL_LENGTH));
            if (!X12Envelope::isComplete($head, $tail)) {
                // Most likely the partner is still writing it; it is tried again next run.
                return [$type, new StoreOutcome(ReceivedStatus::Failed, message: 'The file does not end with a complete interchange')];
            }
            return [$type, $this->store->store($type, $temp, $file->name)];
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }

    private static function path(string $folder, string $name): string
    {
        return rtrim($folder, '/') . '/' . $name;
    }
}
