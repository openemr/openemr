<?php

/**
 * An encounter whose charges were billed before an insurance policy was on
 * file for its date of service, and so never went to that policy's payer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Reopen;

final readonly class ReopenCandidate
{
    /**
     * @param int     $encounter     form_encounter.encounter
     * @param string  $dateOfService Y-m-d
     * @param ?string $lastBilledTo  name of the insurance company the latest billed claim went to, or null
     *                               when that claim had no payer (marked as billed without one)
     * @param string  $balance       charges less payments and adjustments, as "%.2f"
     */
    public function __construct(
        public int $encounter,
        public string $dateOfService,
        public ?string $lastBilledTo,
        public string $balance,
    ) {
    }

    /**
     * @return array{encounter: int, date: string, last_billed_to: ?string, balance: string}
     */
    public function toArray(): array
    {
        return [
            'encounter' => $this->encounter,
            'date' => $this->dateOfService,
            'last_billed_to' => $this->lastBilledTo,
            'balance' => $this->balance,
        ];
    }
}
