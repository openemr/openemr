<?php

/**
 * A patient's encounters with their billing totals, loaded once so the
 * patient, insurance and collection balances can all be read from them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\PatientBalance;

final readonly class PatientBalanceLedger
{
    /**
     * @param list<EncounterBalance> $encounters
     */
    public function __construct(public array $encounters)
    {
    }

    /**
     * The balance as get_patient_balance() reports it: two decimals, no
     * thousands separator.
     *
     * @param bool $withInsurance    count what insurance still owes too
     * @param bool $inCollectionOnly only encounters marked in collection
     * @return numeric-string
     */
    public function balance(bool $withInsurance, bool $inCollectionOnly = false): string
    {
        $balance = 0.0;
        foreach ($this->encounters as $encounter) {
            if ($inCollectionOnly && !$encounter->inCollection) {
                continue;
            }
            $balance += $encounter->amountDue($withInsurance);
        }
        /** @var numeric-string */
        return sprintf('%01.2f', $balance);
    }
}
