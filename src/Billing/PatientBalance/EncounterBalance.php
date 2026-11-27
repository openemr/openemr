<?php

/**
 * One encounter's billing totals, as the patient balance reads them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\PatientBalance;

final readonly class EncounterBalance
{
    /**
     * @param int   $lastLevelClosed         form_encounter.last_level_closed
     * @param int   $insuranceCount          policies in force on the encounter's day,
     *                                       counted from primary until a level is missing
     * @param bool  $inCollection            form_encounter.in_collection is 1
     * @param float $charges                 active billing fees
     * @param float $copayCharges            active billing fees with code_type copay
     * @param float $drugSales               drug_sales fees
     * @param float $payments                ar_activity payments, all payers
     * @param float $adjustments             ar_activity adjustments, all payers
     * @param float $patientPayments         ar_activity payments with payer_type 0
     */
    public function __construct(
        public int $lastLevelClosed,
        public int $insuranceCount,
        public bool $inCollection,
        public float $charges,
        public float $copayCharges,
        public float $drugSales,
        public float $payments,
        public float $adjustments,
        public float $patientPayments,
    ) {
    }

    /**
     * What this encounter adds to the balance.
     *
     * Without insurance, an encounter still out to insurance (fewer levels
     * closed than policies in force) only counts its copay charges less the
     * patient's payments. Every other encounter counts all charges and drug
     * sales less all payments and adjustments.
     */
    public function amountDue(bool $withInsurance): float
    {
        if (!$withInsurance && $this->lastLevelClosed < $this->insuranceCount) {
            return $this->copayCharges - $this->patientPayments;
        }
        return $this->charges + $this->drugSales - $this->payments - $this->adjustments;
    }
}
