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
 * For each partner and each folder it has set: list the folder, skip what an
 * earlier run settled, download the rest one at a time, and store a file only
 * when all of it arrived and it is the kind of X12 file the folder is for. A
 * stored file is moved to the partner's worked folder when it has one;
 * otherwise it stays on the remote and is skipped next run by its record.
 */
final readonly class X12RemoteReceiver
{
    public function __construct(
        private RemoteDirectoryOpener $opener,
        private ReceivedFileLog $log,
        private ReceivedFileStore $store,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private string $tempDirectory,
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
            $remote = $this->opener->open($partner);
            if ($remote === null) {
                $summary->partnersUnreachable++;
                continue;
            }
            foreach ($partner->folders as $typeValue => $folder) {
                $this->receiveFolder($partner, X12FileType::from($typeValue), $folder, $remote, $summary);
            }
        }
        return $summary;
    }

    private function receiveFolder(
        ReceivePartner $partner,
        X12FileType $type,
        string $folder,
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
            $outcome = $this->receiveFile($type, $folder, $file, $remote);
            $this->log->record($partner->id, $folder, $file, $type, $outcome, $this->clock->now());
            $summary->count($outcome->status);
            if ($outcome->status !== ReceivedStatus::Stored || $partner->workedFolder === '') {
                continue;
            }
            if ($remote->move($folder . '/' . $file->name, $partner->workedFolder . '/' . $file->name)) {
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

    private function receiveFile(X12FileType $type, string $folder, RemoteFile $file, RemoteDirectory $remote): StoreOutcome
    {
        if ($file->name === '' || str_contains($file->name, '/') || str_starts_with($file->name, '.')) {
            return new StoreOutcome(ReceivedStatus::Rejected, message: 'The remote file name cannot be used');
        }
        $temp = tempnam($this->tempDirectory, 'x12r');
        if ($temp === false) {
            return new StoreOutcome(ReceivedStatus::Failed, message: 'No temporary file could be created');
        }
        try {
            $downloaded = $remote->download($folder . '/' . $file->name, $temp);
            clearstatcache(true, $temp);
            if (!$downloaded || filesize($temp) !== $file->size) {
                return new StoreOutcome(ReceivedStatus::Failed, message: 'The download did not complete');
            }
            $head = file_get_contents($temp, false, null, 0, X12Envelope::HEAD_LENGTH);
            if (X12Envelope::groupCode(is_string($head) ? $head : '') !== $type->groupCode()) {
                return new StoreOutcome(
                    ReceivedStatus::Rejected,
                    message: 'Not an X12 ' . $type->label() . ' file',
                );
            }
            return $this->store->store($type, $temp, $file->name);
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }
}
