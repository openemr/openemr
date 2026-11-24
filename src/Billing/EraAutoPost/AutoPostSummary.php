<?php

/**
 * Counts from one unattended posting run, for the log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

final class AutoPostSummary
{
    public int $files = 0;
    public int $filesUnreadable = 0;
    public int $filesInterrupted = 0;
    public int $checksPosted = 0;
    public int $checksHeld = 0;
    public int $claimsPosted = 0;
    public int $claimsForReview = 0;
    public int $claimsSkipped = 0;

    public function count(CheckRecord $check): void
    {
        match ($check->status) {
            CheckStatus::Posted => $this->checksPosted++,
            CheckStatus::Held => $this->checksHeld++,
        };
        foreach ($check->claims as $claim) {
            match ($claim->outcome) {
                ClaimOutcome::Posted => $this->claimsPosted++,
                ClaimOutcome::Review => $this->claimsForReview++,
                ClaimOutcome::Skipped => $this->claimsSkipped++,
            };
        }
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'files' => $this->files,
            'filesUnreadable' => $this->filesUnreadable,
            'filesInterrupted' => $this->filesInterrupted,
            'checksPosted' => $this->checksPosted,
            'checksHeld' => $this->checksHeld,
            'claimsPosted' => $this->claimsPosted,
            'claimsForReview' => $this->claimsForReview,
            'claimsSkipped' => $this->claimsSkipped,
        ];
    }
}
