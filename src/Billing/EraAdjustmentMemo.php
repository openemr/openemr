<?php

/**
 * The memo ERA posting stores for a patient-responsibility adjustment (CAS
 * group PR) or a non-primary payer's note: a zero-dollar ar_activity row
 * that keeps what the payer said without crediting the claim.
 *
 * Claim::payerAdjustments() reads these memos back when it builds a
 * secondary claim, with the patterns below; keep the two in step.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final class EraAdjustmentMemo
{
    /**
     * Matches a primary payer's other patient responsibility, with or without
     * the reason code ("Ins1 ptresp 119: 160.00", or "Ins1 ptresp: 160.00" as
     * posted before the code was kept); group 1 is the amount.
     */
    public const PATIENT_RESPONSIBILITY_PATTERN = '/ptresp(?: \w+)?: (\S+)/i';

    /**
     * The memo for one adjustment. Primary payer: deductible (1), coinsurance (2)
     * and copay (3) by name, any other reason as "ptresp" with its code. Other
     * payers: a note with the code. Kept to about 25 characters so it fits on
     * statements.
     *
     * @param string $inslabel 'Ins1', 'Ins2' or 'Ins3'
     */
    public static function forAdjustment(string $inslabel, bool $primary, string $reasonCode, float $amount): string
    {
        // A missing code leaves no gap before the colon ("Ins1 ptresp: 160.00").
        $code = $reasonCode === '' ? '' : " $reasonCode";
        $prefix = match (true) {
            !$primary => "$inslabel note$code: ",
            $reasonCode === '1' => "$inslabel dedbl: ",
            $reasonCode === '2' => "$inslabel coins: ",
            $reasonCode === '3' => "$inslabel copay: ",
            default => "$inslabel ptresp$code: ",
        };
        return $prefix . sprintf('%.2f', $amount);
    }
}
