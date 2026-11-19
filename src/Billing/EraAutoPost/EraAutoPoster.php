<?php

/**
 * Posts received 835s without a person at the screen, and records what it did.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

/**
 * Each waiting 835 is taken once. A file that cannot be parsed to its end posts
 * nothing. Otherwise each check is either posted (its claim payments go through
 * the posting engine, which posts what it would post for a person and refuses or
 * flags the rest) or held whole:
 *
 * - its claim payments less its provider-level adjustments do not equal the check amount;
 * - none of its claims leads to an insurance company to post it for;
 * - the check was already posted for a different insurance company.
 *
 * One row is recorded per check and per claim payment. A file is never taken a
 * second time, so running again changes nothing; a file an error stopped part way
 * is left marked as interrupted for a person to look at, since its checks may be
 * partly posted.
 */
final readonly class EraAutoPoster
{
    public function __construct(
        private AutoPostLog $log,
        private RemittanceReader $reader,
        private CheckPoster $poster,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private string $eraDirectory,
    ) {
    }

    /**
     * @param \DateTimeImmutable $since only files received from then on are posted, so turning
     *                                  the setting on does not post a partner's older files
     */
    public function run(\DateTimeImmutable $since): AutoPostSummary
    {
        $summary = new AutoPostSummary();
        foreach ($this->log->pending($since) as $file) {
            if (!$this->log->take($file->receivedId)) {
                continue;
            }
            $summary->files++;
            try {
                $status = $this->postFile($file, $summary);
            } catch (\RuntimeException $e) {
                $status = AutoPostFileStatus::Interrupted;
                $this->logger->error('ERA auto-post: a file was interrupted', [
                    'received' => $file->receivedId,
                    'file' => $file->storedName,
                    'exception' => $e,
                ]);
            }
            match ($status) {
                AutoPostFileStatus::Unreadable => $summary->filesUnreadable++,
                AutoPostFileStatus::Interrupted => $summary->filesInterrupted++,
                AutoPostFileStatus::Running, AutoPostFileStatus::Done => null,
            };
            $this->log->finish($file->receivedId, $status);
        }
        return $summary;
    }

    private function postFile(PendingRemittance $file, AutoPostSummary $summary): AutoPostFileStatus
    {
        $path = $this->eraDirectory . '/' . basename($file->storedName);
        $remittance = is_file($path) ? $this->reader->read($path) : new Remittance([], 'The stored file is missing');
        if ($remittance->error !== '') {
            $this->logger->warning('ERA auto-post: the file could not be read as an 835', [
                'received' => $file->receivedId,
                'file' => $file->storedName,
                'error' => $remittance->error,
            ]);
            return AutoPostFileStatus::Unreadable;
        }
        foreach ($remittance->checks as $index => $check) {
            $record = $this->postCheck($check, $file->postUser);
            $this->log->record($file->receivedId, $index + 1, $record, $this->clock->now());
            $summary->count($record);
        }
        return AutoPostFileStatus::Done;
    }

    private function postCheck(RemittanceCheck $check, int $postUser): CheckRecord
    {
        if (!$check->isBalanced()) {
            return CheckRecord::held($check, HoldReason::OutOfBalance);
        }
        $payerId = $this->poster->payerFor($check);
        if ($payerId === '') {
            return CheckRecord::held($check, HoldReason::NoPayer);
        }
        if ($this->poster->postedForAnotherPayer($check, $payerId)) {
            return CheckRecord::held($check, HoldReason::PostedForAnotherPayer, $payerId);
        }
        return $this->poster->post($check, $payerId, $postUser, $this->clock->now());
    }
}
