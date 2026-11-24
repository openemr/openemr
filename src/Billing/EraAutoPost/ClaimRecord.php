<?php

/**
 * What an unattended posting run did with one claim payment, as it is stored.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ClaimPosting;
use OpenEMR\Billing\EraPosting\ClaimPostingResult;

final readonly class ClaimRecord
{
    /** The reason stored for every claim payment of a check that was held. */
    public const CHECK_HELD = 'check_held';

    /**
     * @param list<string> $reasons why it needs review or was skipped; none when posted
     */
    public function __construct(
        public ClaimPayment $claim,
        public ClaimOutcome $outcome,
        public array $reasons,
        public int $pid,
        public int $encounter,
        public int $level,
        public float $paymentsPosted = 0.0,
        public float $adjustmentsPosted = 0.0,
        public float $chargesAdded = 0.0,
        public bool $levelClosed = false,
        public bool $secondaryQueued = false,
    ) {
    }

    /**
     * A claim payment whose check was held: nothing was looked up or posted for it.
     */
    public static function held(ClaimPayment $claim): self
    {
        return new self($claim, ClaimOutcome::Review, [self::CHECK_HELD], 0, 0, ClaimPosting::levelFor($claim->claimStatusCode));
    }

    public static function fromResult(ClaimPayment $claim, ClaimPostingResult $result): self
    {
        $outcome = match (true) {
            $result->issues === [] => ClaimOutcome::Posted,
            $result->hasIssue(ClaimIssue::AlreadyPosted) => ClaimOutcome::Skipped,
            default => ClaimOutcome::Review,
        };
        return new self(
            claim: $claim,
            outcome: $outcome,
            reasons: array_map(self::reason(...), $result->issues),
            pid: $result->pid,
            encounter: $result->encounter,
            level: $result->level,
            paymentsPosted: $result->paymentsPosted,
            adjustmentsPosted: $result->adjustmentsPosted,
            chargesAdded: $result->chargesAdded,
            levelClosed: $result->levelClosed,
            secondaryQueued: $result->secondaryQueued,
        );
    }

    /**
     * The value stored in era_auto_post_claim.reasons for an issue.
     */
    public static function reason(ClaimIssue $issue): string
    {
        return match ($issue) {
            ClaimIssue::ClaimNotFound => 'claim_not_found',
            ClaimIssue::AlreadyPosted => 'already_posted',
            ClaimIssue::Denied => 'denied',
            ClaimIssue::Reversal => 'reversal',
            ClaimIssue::ChargeMismatch => 'charge_mismatch',
            ClaimIssue::CodeNotOnInvoice => 'code_not_on_invoice',
            ClaimIssue::UnpaidAdjustment => 'unpaid_adjustment',
            ClaimIssue::LinesPended => 'lines_pended',
        };
    }
}
