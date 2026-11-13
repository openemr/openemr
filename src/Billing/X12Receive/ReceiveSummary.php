<?php

/**
 * Counts from one run of the receive service.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

final class ReceiveSummary
{
    public int $partners = 0;
    public int $partnersUnreachable = 0;
    public int $partnersInterrupted = 0;
    public int $foldersUnreadable = 0;
    public int $stored = 0;
    public int $duplicates = 0;
    public int $rejected = 0;
    public int $failed = 0;
    public int $moved = 0;

    public function count(ReceivedStatus $status): void
    {
        match ($status) {
            ReceivedStatus::Stored => $this->stored++,
            ReceivedStatus::Duplicate => $this->duplicates++,
            ReceivedStatus::Rejected => $this->rejected++,
            ReceivedStatus::Failed => $this->failed++,
        };
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'partners' => $this->partners,
            'partnersUnreachable' => $this->partnersUnreachable,
            'partnersInterrupted' => $this->partnersInterrupted,
            'foldersUnreadable' => $this->foldersUnreadable,
            'stored' => $this->stored,
            'duplicates' => $this->duplicates,
            'rejected' => $this->rejected,
            'failed' => $this->failed,
            'moved' => $this->moved,
        ];
    }
}
