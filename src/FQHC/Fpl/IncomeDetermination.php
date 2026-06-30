<?php

/**
 * A patient's income determination input for the FPL calculation.
 *
 * Household size and annual income are nullable because intake data is often
 * incomplete, and `unknown` lets staff explicitly record a declined/unknown
 * determination. Any of these makes the result Unknown rather than a guessed
 * band. Invalid values (size < 1, negative income) are rejected at construction
 * so downstream code never sees them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

use DomainException;

final readonly class IncomeDetermination
{
    public function __construct(
        public ?int $householdSize,
        public ?float $annualIncome,
        public bool $unknown = false,
    ) {
        if ($householdSize !== null && $householdSize < 1) {
            throw new DomainException('Household size must be at least 1');
        }
        if ($annualIncome !== null && $annualIncome < 0.0) {
            throw new DomainException('Annual income cannot be negative');
        }
    }

    public function isDeterminable(): bool
    {
        return !$this->unknown
            && $this->householdSize !== null
            && $this->annualIncome !== null;
    }
}
