<?php

/**
 * Display-ready summary of a patient's income / FPL determination.
 *
 * What the Snapshot's income card shows: the inputs (household size, income),
 * the computed FPL percentage and band, and the resulting sliding-fee tier.
 * `recorded` is false when nothing usable has been captured yet (the card then
 * shows an empty-state prompting entry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Income;

final readonly class IncomeSummary
{
    public function __construct(
        public bool $recorded,
        public ?int $householdSize,
        public ?string $annualIncomeDisplay,
        public ?int $fplPercent,
        public string $bandLabel,
        public string $tierLabel,
    ) {
    }
}
