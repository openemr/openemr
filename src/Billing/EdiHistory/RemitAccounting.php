<?php

/**
 * Accounting helpers for X12 835 remittance advice display.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

class RemitAccounting
{
    /**
     * Determine whether an 835 remit's provider fee balances against the
     * payment and adjustment totals. Compares in integer cents so the
     * result is not subject to float representation drift, e.g.
     * 0.10 + 0.20 != 0.30 under float equality.
     *
     * @param array<string, float> $acctng totals keyed fee, pmt, clmadj, svcadj, svcptrsp, plbadj
     */
    public static function isBalanced(array $acctng): bool
    {
        $accounted = $acctng['pmt'] + $acctng['clmadj'] + $acctng['svcadj'] + $acctng['svcptrsp'] + $acctng['plbadj'];
        return (int) round($acctng['fee'] * 100) === (int) round($accounted * 100);
    }
}
