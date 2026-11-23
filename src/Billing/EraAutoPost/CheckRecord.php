<?php

/**
 * What an unattended posting run did with one check, as it is stored.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

final readonly class CheckRecord
{
    /**
     * @param string            $payerId   the insurance company the check was posted for, or ''
     * @param int               $sessionId the payment session posted to; 0 when held
     * @param list<ClaimRecord> $claims    one per claim payment, in the file's order
     */
    public function __construct(
        public RemittanceCheck $check,
        public CheckStatus $status,
        public ?HoldReason $holdReason,
        public string $payerId,
        public int $sessionId,
        public array $claims,
    ) {
    }

    public static function held(RemittanceCheck $check, HoldReason $reason, string $payerId = ''): self
    {
        return new self($check, CheckStatus::Held, $reason, $payerId, 0, array_map(ClaimRecord::held(...), $check->claims));
    }
}
